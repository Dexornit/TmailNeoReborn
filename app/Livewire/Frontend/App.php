<?php

namespace App\Livewire\Frontend;

use App\Models\Message;
use App\Services\TMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class App extends Component
{
    public $messages = [];

    public $deleted = [];

    public $error = '';

    public $email;

    public $initial;

    public $overflow = false;

    // NEW: Pagination properties
    public $page = 1;

    public $hasMore = true;

    public $loadingMore = false;

    protected $listeners = ['fetchMessages' => 'fetch', 'syncEmail'];

    public function mount(): void
    {
        $this->email = TMail::getEmail();
        $this->initial = false;
    }

    public function syncEmail($email): void
    {
        $this->email = $email;
    }

    public function fetch($pageNumber = 1): void
    {
        /**
         * Optimasi vs versi asli:
         *
         *  1. Satu koneksi IMAP di-share untuk fetch to + cc.
         *     Sebelumnya: 2 koneksi terpisah dibuka per fetch.
         *
         *  2. expunge() dipanggil SATU kali di sini setelah semua fetch
         *     selesai — bukan di dalam getMessages() per call.
         *
         *  3. resetConnection() di finally — pastikan static cache bersih
         *     untuk request berikutnya.
         *
         *  4. Log error ke Laravel Log untuk debugging.
         *
         * NEW Optimizations:
         *  5. Pagination: Load email dalam batch (default 20 per page)
         *  6. Memory Bypass: Skip parsing untuk email yang sudah ada di UI
         *  7. UNSEEN Filter: Fetch hanya email baru yang belum dibaca
         */
        try {
            $count = count($this->messages);
            $useImap = config('app.settings.engine') !== 'delivery';
            $ccCheck = $useImap && config('app.settings.imap.cc_check', false);

            // NEW: Pagination settings
            $paginationEnabled = config('tmail.enable_pagination', true);
            $limit = (int) config('tmail.pagination_limit', 20);
            $offset = ($pageNumber - 1) * $limit;

            // UNSEEN filter is OFF by default. The previous behavior of
            // filtering by UNSEEN on the first page caused already-seen
            // messages to disappear on refresh because IMAP marks messages
            // as SEEN after the first fetch. Admins can opt back in via
            // config('tmail.fetch_unseen_only').
            $unseenOnly = (bool) config('tmail.fetch_unseen_only', false);

            // ── Buka SATU koneksi, di-share ke to + cc ────────────────
            $connection = $useImap ? TMail::connectMailBox() : null;

            // ── Siapkan pesan memori agar tidak di-parse ulang oleh IMAP ──
            $existingMsgs = [];
            $memoryBypassEnabled = config('tmail.enable_memory_bypass', true);
            if ($memoryBypassEnabled) {
                foreach ($this->messages as $m) {
                    // Key menggunakan ID email dari IMAP
                    $existingMsgs[$m['id']] = $m;
                }
            }

            // ── Fetch to ──────────────────────────────────────────────
            $toResult = TMail::getMessages(
                $this->email,
                'to',
                $this->deleted,
                $connection,
                $existingMsgs,
                $unseenOnly,
                $paginationEnabled ? $offset : 0,
                $paginationEnabled ? $limit : (int) config('app.settings.fetch_messages_limit', 100)
            );

            // ── Fetch cc (opsional, koneksi sama) ─────────────────────
            $ccResult = $ccCheck
                ? TMail::getMessages(
                    $this->email,
                    'cc',
                    $this->deleted,
                    $connection,
                    $existingMsgs,
                    $unseenOnly,
                    $paginationEnabled ? $offset : 0,
                    $paginationEnabled ? $limit : (int) config('app.settings.fetch_messages_limit', 100)
                )
                : ['data' => [], 'notifications' => [], 'has_deleted' => false];

            // ── Single expunge setelah semua fetch selesai ────────────
            if ($useImap && $connection) {
                $needsExpunge = ($toResult['has_deleted'] ?? false)
                             || ($ccResult['has_deleted'] ?? false);
                if ($needsExpunge) {
                    $connection->expunge();
                }
            }

            $this->deleted = [];

            $newMessages = array_merge(
                $toResult['data'] ?? [],
                $ccResult['data'] ?? []
            );

            // NEW: Pagination - append or replace messages
            if ($paginationEnabled && $pageNumber > 1) {
                // Append to existing messages
                $this->messages = array_merge($this->messages, $newMessages);
            } else {
                // Replace messages (first page)
                $this->messages = $newMessages;
            }

            // ── Sort ──────────────────────────────────────────────────
            // FastOTP: Mengurutkan pesan dari gabungan to+cc menjadi descending by ID
            usort($this->messages, function ($a, $b) {
                return $b['id'] <=> $a['id'];
            });

            // NEW: Check if has more emails
            $this->hasMore = count($newMessages) >= $limit;
            $this->page = $pageNumber;

            $notifications = array_merge(
                $toResult['notifications'] ?? [],
                $ccResult['notifications'] ?? []
            );

            // ── Overflow detection ────────────────────────────────────
            if (count($notifications)) {
                if ($this->overflow === false && count($this->messages) === $count) {
                    $this->overflow = true;
                }
            } else {
                $this->overflow = false;
            }

            foreach ($notifications as $notification) {
                $this->dispatch('showNewMailNotification', $notification);
            }

            if ($useImap && count($notifications) > 0) {
                TMail::incrementMessagesStats(count($notifications));
            }

        } catch (\Exception $e) {
            if (Auth::check() && Auth::user()->role == 7) {
                $this->error = $e->getMessage();
            } else {
                $this->error = 'Not able to connect to Mail Server';
            }

            Log::error('[TMail] fetch() gagal: '.$e->getMessage(), [
                'email' => $this->email,
            ]);
        } finally {
            // Reset static connection cache — penting untuk PHP-FPM agar
            // tidak ada koneksi stale yang dipakai di request berikutnya.
            if ($useImap) {
                TMail::resetConnection();
            }
        }

        $this->dispatch('stopLoader');
        $this->dispatch('loadDownload');
        $this->initial = true;
    }

    public function delete($messageId): void
    {
        if (config('app.settings.engine') === 'delivery') {
            Message::find($messageId)?->delete();
        }

        $this->deleted[] = $messageId;

        foreach ($this->messages as $key => $message) {
            if ($message['id'] == $messageId) {
                $this->rrmdir('./tmp/attachments/'.$messageId);
                unset($this->messages[$key]);
                break; // ID unik, tidak perlu lanjut loop
            }
        }
    }

    /**
     * Load more emails (pagination).
     * Called when user clicks "Load More" button or scrolls to bottom.
     */
    public function loadMore(): void
    {
        if (! $this->hasMore || $this->loadingMore) {
            return;
        }

        $this->loadingMore = true;
        $this->fetch($this->page + 1);
        $this->loadingMore = false;
    }

    /**
     * Download attachment on-demand (lazy load).
     * Called when user clicks download button on attachment.
     *
     * @param  int  $messageNumber  IMAP message number
     * @param  string  $filename  Attachment filename
     */
    public function downloadAttachment(int $messageNumber, string $filename): void
    {
        try {
            $result = TMail::downloadAttachment($messageNumber, $filename);

            if ($result['success']) {
                // Update attachment in messages array
                foreach ($this->messages as $key => $message) {
                    if ($message['id'] == $messageNumber) {
                        foreach ($this->messages[$key]['attachments'] as $attKey => $attachment) {
                            if ($attachment['file'] === $filename) {
                                $this->messages[$key]['attachments'][$attKey]['downloaded'] = true;
                                $this->messages[$key]['attachments'][$attKey]['url'] = $result['url'];
                                break 2;
                            }
                        }
                    }
                }

                // Emit event to frontend for download
                $this->dispatch('attachmentDownloaded', [
                    'messageId' => $messageNumber,
                    'filename' => $filename,
                    'url' => $result['url'],
                ]);
            } else {
                // Emit error event
                $this->dispatch('attachmentDownloadFailed', [
                    'messageId' => $messageNumber,
                    'filename' => $filename,
                    'error' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[TMail] Download attachment failed in Livewire', [
                'message_number' => $messageNumber,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('attachmentDownloadFailed', [
                'messageId' => $messageNumber,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('frontend.themes.'.config('app.settings.theme').'.components.app');
    }

    private function rrmdir($dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $path = $dir.DIRECTORY_SEPARATOR.$object;
            if (is_dir($path) && ! is_link($dir.'/'.$object)) {
                $this->rrmdir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
