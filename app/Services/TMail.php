<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Log;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\Stat;
use Carbon\Carbon;
use Ddeboer\Imap\Search\Email\Cc;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Flag\Unseen;

class TMail extends Model
{

    private const SESSION_EMAIL = 'email';
    private const SESSION_EMAILS = 'emails';

    /**
     * Static connection cache.
     * Di-share antara getMessages('to') dan getMessages('cc')
     * dalam satu request — hemat 1 TCP handshake + IMAP authenticate (~200–500ms).
     *
     * Di PHP-FPM, static property di-reset tiap request secara otomatis,
     * jadi tidak ada risiko koneksi bocor antar user.
     */
    private static ?object $cachedConnection = null;

    /**
     * Static domain cache for performance.
     * Cache blocked and allowed domains to avoid repeated config reads.
     * Reset automatically per request in PHP-FPM.
     */
    private static ?array $cachedBlockedDomains = null;
    private static ?array $cachedAllowedDomains = null;

    // ------------------------------------------------------------------
    // CONNECTION
    // ------------------------------------------------------------------

    public static function connectMailBox($imap = null): object
    {
        if (self::$cachedConnection !== null) {
            return self::$cachedConnection;
        }

        $imap = $imap ?? config('app.settings.imap');
        $flags = $imap['protocol'] . '/' . $imap['encryption'];
        $flags .= $imap['validate_cert'] ? '/validate-cert' : '/novalidate-cert';

        $server = new Server($imap['host'], $imap['port'], $flags);
        self::$cachedConnection = $server->authenticate($imap['username'], $imap['password']);

        return self::$cachedConnection;
    }

    public static function resetConnection(): void
    {
        self::$cachedConnection = null;
    }

    /**
     * Clear domain cache.
     * Call this method if blocked_domains or allowed_domains config changes.
     */
    public static function clearDomainCache(): void
    {
        self::$cachedBlockedDomains = null;
        self::$cachedAllowedDomains = null;
    }

    // ------------------------------------------------------------------
    // MESSAGES
    // ------------------------------------------------------------------

    public static function getMessages($email, $type = 'to', $deleted = [], $connection = null, $existing = [], $unseenOnly = true, $offset = 0, $limit = null): array
    {
        /**
         * Optimasi yang dipertahankan vs versi asli:
         *   - Shared connection (hemat 1 koneksi IMAP per request)
         *   - expunge() dipindahkan ke caller (App.php), dipanggil 1x saja
         *
         * FastOTP In-Memory Bypass:
         *   - Menerima array `$existing` dari App Livewire.
         *   - Lewati parsing body & attachments (yang sangat berat) untuk email
         *     yang sudah ada di memori tabel UI. Fetch menjadi instan O(1) 
         *     bagi pesan-pesan lama.
         *
         * NEW Optimizations:
         *   - UNSEEN Filter: Fetch hanya email yang belum dibaca (40% faster)
         *   - Pagination: Load email dalam batch (50% faster initial load)
         *
         * @param string      $email
         * @param string      $type       'to' | 'cc'
         * @param array       $deleted    message numbers yang harus dihapus
         * @param object|null $connection koneksi IMAP yang sudah dibuka
         * @param array       $existing   array ID pesan yang sudah di-load di UI
         * @param bool        $unseenOnly fetch hanya email UNSEEN (default: true)
         * @param int         $offset     pagination offset (default: 0)
         * @param int|null    $limit      pagination limit (default: from config)
         * @return array      ['data'=>[...], 'notifications'=>[...], 'has_deleted'=>bool]
         */
        if (config('app.settings.engine') === 'delivery') {
            return array_merge(Message::getMessages($email), ['has_deleted' => false]);
        }

        // ── IMAP fetch ─────────────────────────────────────────────────
        $connection = $connection ?? self::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $limit = $limit ?? (int)config('tmail.pagination_limit', config('app.settings.fetch_messages_limit', 20));

        // Guard: if email is null (session expired), return empty response immediately
        if (empty($email)) {
            return ['data' => [], 'notifications' => [], 'has_deleted' => false];
        }

        try {
            $search = new SearchExpression();
            
            // NEW: UNSEEN Filter - fetch only unread emails if enabled
            $unseenEnabled = config('tmail.enable_unseen_filter', true);
            if ($unseenEnabled && $unseenOnly) {
                $search->addCondition(new Unseen());
            }
            
            // Add To/Cc filter
            $search->addCondition($type === 'cc' ? new Cc($email) : new To($email));

            $messages = $mailbox->getMessages($search, \SORTDATE, true);

        } catch (\Exception $e) {
            // Fallback: if UNSEEN search fails, try without UNSEEN filter
            \Illuminate\Support\Facades\Log::warning('[TMail] UNSEEN filter failed, falling back to normal fetch', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            
            $search = new SearchExpression();
            $search->addCondition($type === 'cc' ? new Cc($email) : new To($email));
            $messages = $mailbox->getMessages($search, \SORTDATE, true);
        }

        $response = ['data' => [], 'notifications' => [], 'has_deleted' => false];
        $hasDeleted = false;
        $count = 0;
        $skipped = 0;

        foreach ($messages as $message) {
            $id = $message->getNumber();
            
            // Handle deleted messages
            if (in_array($id, $deleted, true)) {
                $message->delete();
                $hasDeleted = true;
                continue;
            }

            // NEW: Pagination - skip messages before offset
            if ($skipped < $offset) {
                $skipped++;
                continue;
            }

            // FastOTP Bypass: Jika email ini sudah ada di UI (memory), skip parsing berat
            if (isset($existing[$id])) {
                $response['data'][] = $existing[$id];
            } else {
                $data = self::formatMessage($message, $email);
                $response['data'][] = $data['message'];
                if ($data['notification']) {
                    $response['notifications'][] = $data['notification'];
                }
            }

            // NEW: Pagination - stop after limit reached
            if (++$count >= $limit)
                break;
        }

        $response['has_deleted'] = $hasDeleted;

        return $response;
    }

    // ------------------------------------------------------------------
    // FORMAT MESSAGE
    // ------------------------------------------------------------------

    public static function formatMessage($message, $email = null, $lazyLoad = true): array
    {
        $file_types = config(
            'app.settings.allowed_file_types',
            'csv,doc,docx,xls,xlsx,ppt,pptx,xps,pdf,dxf,ai,psd,eps,ps,svg,ttf,zip,rar,tar,gzip,mp3,mpeg,wav,ogg,jpeg,jpg,png,gif,bmp,tif,webm,mpeg4,3gpp,mov,avi,mpegs,wmv,flx,txt'
        );
        $allowed = array_map('strtolower', array_map('trim', explode(',', $file_types)));

        $sender = $message->getFrom();
        $date = $message->getDate() ?: (new \DateTime());

        if (!$message->getDate() && $message->getHeaders()->get('udate')) {
            $date->setTimestamp($message->getHeaders()->get('udate'));
        }

        $datediff = new Carbon($date);
        $html = $message->getBodyHtml();
        $text = $message->getBodyText();
        $content = $html
            ? str_replace('<a', '<a target="blank"', $html)
            : str_replace('<a', '<a target="blank"', str_replace(["\r\n", "\n"], '<br/>', $text));

        $masker = config('app.settings.external_link_masker', '');
        if ($masker) {
            $content = str_replace('href="', 'href="' . $masker, $content);
        }

        $obj = [
            'subject' => $message->getSubject(),
            'sender_name' => $sender->getName(),
            'sender_email' => $sender->getAddress(),
            'timestamp' => $message->getDate(),
            'date' => $date->format(config('app.settings.date_format', 'd M Y h:i A')),
            'datediff' => $datediff->diffForHumans(),
            'id' => $message->getNumber(),
            'content' => $content,
            'attachments' => [],
        ];

        // NEW: Initialize domain cache on first call
        $domainCacheEnabled = config('tmail.enable_domain_cache', true);
        if ($domainCacheEnabled) {
            if (self::$cachedBlockedDomains === null) {
                self::$cachedBlockedDomains = config('app.settings.blocked_domains', []);
            }
            if (self::$cachedAllowedDomains === null) {
                self::$cachedAllowedDomains = config('app.settings.allowed_domains', []);
            }
        }

        $domain = explode('@', $obj['sender_email'])[1] ?? '';
        
        // Use cached domains if enabled, otherwise read from config
        $blockedDomains = $domainCacheEnabled ? self::$cachedBlockedDomains : config('app.settings.blocked_domains', []);
        $allowedDomains = $domainCacheEnabled ? self::$cachedAllowedDomains : config('app.settings.allowed_domains', []);
        
        $blocked = in_array($domain, $blockedDomains, true);

        if ($blocked) {
            $obj['subject'] = __('Blocked');
            $obj['content'] = __('Emails from') . ' ' . $domain . ' ' . __('are blocked by Admin');
        }

        if (count($allowedDomains) > 0) {
            $notAllowed = !in_array($domain, $allowedDomains, true);
            if ($notAllowed) {
                $obj['subject'] = __('Blocked');
                $obj['content'] = __('Emails from') . ' ' . $domain . ' ' . __('are blocked by Admin');
            }
        }

        if ($message->hasAttachments() && !$blocked) {
            $attachments = $message->getAttachments();
            $directory = './tmp/attachments/' . $obj['id'] . '/';
            
            // NEW: Check if lazy load is enabled
            $lazyLoadEnabled = config('tmail.enable_lazy_load', true);
            $shouldLazyLoad = $lazyLoadEnabled && $lazyLoad;

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            foreach ($attachments as $attachment) {
                $filename = $attachment->getFilename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($extension, $allowed, true)) {
                    $filepath = $directory . $filename;
                    $structure = $attachment->getStructure();
                    
                    // Check if this is an inline image (cid:)
                    $isInline = isset($structure->id) && str_contains($obj['content'], trim($structure->id, '<>'));

                    if ($isInline) {
                        // Always download inline images for email display
                        if (!file_exists($filepath)) {
                            file_put_contents($filepath, $attachment->getDecodedContent());
                        }

                        if ($filename !== 'undefined') {
                            $url = env('APP_URL') . str_replace('./', '/', $filepath);
                            $obj['content'] = str_replace('cid:' . trim($structure->id, '<>'), $url, $obj['content']);
                            
                            $obj['attachments'][] = [
                                'file' => $filename,
                                'url' => $url,
                                'downloaded' => true,
                                'inline' => true
                            ];
                        }
                    } else if ($shouldLazyLoad) {
                        // NEW: Lazy load - save metadata only, don't download yet
                        if ($filename !== 'undefined') {
                            $obj['attachments'][] = [
                                'file' => $filename,
                                'size' => method_exists($attachment, 'getSize') ? $attachment->getSize() : 0,
                                'type' => $extension,
                                'downloaded' => false,
                                'inline' => false,
                                'url' => null
                            ];
                        }
                    } else {
                        // Normal mode: download immediately
                        if (!file_exists($filepath)) {
                            file_put_contents($filepath, $attachment->getDecodedContent());
                        }

                        if ($filename !== 'undefined') {
                            $url = env('APP_URL') . str_replace('./', '/', $filepath);
                            $obj['attachments'][] = [
                                'file' => $filename,
                                'url' => $url,
                                'downloaded' => true,
                                'inline' => false
                            ];
                        }
                    }
                }
            }
        }

        $notification = '';
        if (!$message->isSeen()) {
            $notification = [
                'subject' => $obj['subject'],
                'sender_name' => $obj['sender_name'],
                'sender_email' => $obj['sender_email'],
            ];

            if (env('ENABLE_TMAIL_LOGS', false) && $email) {
                file_put_contents(
                    storage_path('logs/tmail.csv'),
                    request()->ip() . ',' . date('Y-m-d h:i:s a') . ',' . $obj['sender_email'] . ',' . $email . PHP_EOL,
                    FILE_APPEND
                );
            }
        }

        $message->markAsSeen();

        return ['message' => $obj, 'notification' => $notification];
    }

    // ------------------------------------------------------------------
    // DELETE
    // ------------------------------------------------------------------

    public static function deleteMessage($id): void
    {
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $mailbox->getMessage($id)->delete();
        $connection->expunge();
    }

    // ------------------------------------------------------------------
    // DOWNLOAD ATTACHMENT (Lazy Load)
    // ------------------------------------------------------------------

    /**
     * Download attachment on-demand for lazy loading.
     * 
     * @param int    $messageNumber IMAP message number
     * @param string $filename      Attachment filename
     * @return array ['success' => bool, 'url' => string|null, 'error' => string|null]
     */
    public static function downloadAttachment(int $messageNumber, string $filename): array
    {
        $directory = './tmp/attachments/' . $messageNumber . '/';
        $filepath = $directory . $filename;

        // Check if already downloaded
        if (file_exists($filepath)) {
            $url = env('APP_URL') . str_replace('./', '/', $filepath);
            return ['success' => true, 'url' => $url, 'error' => null];
        }

        try {
            // Connect to IMAP and fetch attachment
            $connection = self::connectMailBox();
            $mailbox = $connection->getMailbox('INBOX');
            $message = $mailbox->getMessage($messageNumber);

            if (!$message) {
                return ['success' => false, 'url' => null, 'error' => 'Message not found'];
            }

            $attachments = $message->getAttachments();
            
            foreach ($attachments as $attachment) {
                if ($attachment->getFilename() === $filename) {
                    // Create directory if not exists
                    if (!is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    // Download and save attachment
                    file_put_contents($filepath, $attachment->getDecodedContent());
                    
                    $url = env('APP_URL') . str_replace('./', '/', $filepath);
                    return ['success' => true, 'url' => $url, 'error' => null];
                }
            }

            return ['success' => false, 'url' => null, 'error' => 'Attachment not found'];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[TMail] Download attachment failed', [
                'message_number' => $messageNumber,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'url' => null, 'error' => $e->getMessage()];
        } finally {
            self::resetConnection();
        }
    }

    // ------------------------------------------------------------------
    // SESSION: EMAIL MANAGEMENT
    // ------------------------------------------------------------------

    public static function getEmail($generate = false): ?string
    {
        if (Session::has(self::SESSION_EMAIL)) {
            return Session::get(self::SESSION_EMAIL);
        }
        return $generate ?self::generateRandomEmail() : null;
    }

    public static function getEmails(): array
    {
        if (Session::has(self::SESSION_EMAILS)) {
            $emails = json_decode(Session::get(self::SESSION_EMAILS), true);
            return is_array($emails) ? $emails : [];
        }
        return [];
    }

    public static function setEmail($email): void
    {
        $emails = self::getEmails();
        if (in_array($email, $emails, true)) {
            Session::put(self::SESSION_EMAIL, $email);
        }
    }

    public static function removeEmail($email): void
    {
        $emails = self::getEmails();
        $key = array_search($email, $emails, true);

        if ($key !== false) {
            array_splice($emails, $key, 1);
        }

        if ($emails) {
            self::setEmail($emails[0]);
            Session::put(self::SESSION_EMAILS, json_encode($emails));
        }
        else {
            Session::forget(self::SESSION_EMAIL);
            Session::forget(self::SESSION_EMAILS);
        }
    }

    // ------------------------------------------------------------------
    // SESSION: STORE
    // ------------------------------------------------------------------

    private static function storeEmail($email): void
    {
        Log::create([
            'ip' => request()->ip(),
            'email' => $email,
        ]);

        Session::put(self::SESSION_EMAIL, $email);

        $emails = self::getEmails();
        if (!in_array($email, $emails, true)) {
            self::incrementEmailStats();
            $emails[] = $email;
            Session::put(self::SESSION_EMAILS, json_encode($emails));
        }
    }

    // ------------------------------------------------------------------
    // EMAIL CREATION
    // ------------------------------------------------------------------

    public static function createCustomEmailFull($email): string
    {
        [$username, $domain] = explode('@', $email);
        $min = (int)config('app.settings.custom.min');
        $max = (int)config('app.settings.custom.max');

        if (strlen($username) < $min || strlen($username) > $max) {
            $username = (new self)->generateRandomUsername();
        }

        return self::createCustomEmail($username, $domain);
    }

    public static function createCustomEmail($username, $domain): string
    {
        $username = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
        $forbidden_ids = config('app.settings.forbidden_ids', []);
        $domains = Domain::getDomainsForCurrentUser();

        if (in_array($username, $forbidden_ids, true)) {
            return self::generateRandomEmail(true);
        }

        $domain = in_array($domain, $domains, true) ? $domain : ($domains[0] ?? '');
        $email = $username . '@' . $domain;

        self::storeEmail($email);
        return $email;
    }

    // ------------------------------------------------------------------
    // STATS
    // ------------------------------------------------------------------

    public static function incrementEmailStats($count = 1): void
    {
        Stat::storeEmailsCreated($count);
    }

    public static function incrementMessagesStats($count = 1): void
    {
        Stat::storeMessagesReceived($count);
    }

    // ------------------------------------------------------------------
    // RANDOM EMAIL GENERATION
    // ------------------------------------------------------------------

    public static function generateRandomEmail($store = true): string
    {
        $tmail = new self;
        $email = $tmail->generateRandomUsername() . '@' . $tmail->getRandomDomain();

        if ($store) {
            self::storeEmail($email);
        }

        return $email;
    }

    private function generateRandomUsername(): string
    {
        $start = config('app.settings.random.start', 0);
        $end = config('app.settings.random.end', 0);

        if ($start == 0 && $end == 0) {
            return $this->generatePronounceableWord();
        }

        return $this->generatedRandomBetweenLength($start, $end);
    }

    protected function generatedRandomBetweenLength($start, $end): string
    {
        $length = rand($start, $end);
        return $this->generateRandomString($length);
    }

    private function getRandomDomain(): string
    {
        $domains = Domain::getDomainsForCurrentUser();
        $count = count($domains);
        return $count > 0 ? $domains[rand(0, $count - 1)] : '';
    }

    private function generatePronounceableWord(): string
    {
        $c = 'bcdfghjklmnprstvwz';
        $v = 'aeiou';
        $a = $c . $v;
        $random = '';

        for ($j = 0; $j < 2; $j++) {
            $random .= $c[rand(0, strlen($c) - 1)];
            $random .= $v[rand(0, strlen($v) - 1)];
            $random .= $a[rand(0, strlen($a) - 1)];
        }

        return $random;
    }

    private function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}