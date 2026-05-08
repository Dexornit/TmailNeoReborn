<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Email;
use App\Models\Stat;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailManager
{
    public const RESERVED_USERNAMES = [
        'admin', 'administrator', 'root', 'postmaster', 'hostmaster',
        'webmaster', 'abuse', 'security', 'noreply', 'no-reply',
        'support', 'info', 'sales', 'help', 'mail', 'mailer-daemon',
        'mailer', 'system', 'staff', 'owner', 'noc',
    ];

    /**
     * Create a single email entry.
     *
     * @return Email|null null if duplicate / invalid.
     */
    public static function createOne(string $username, string $domain, ?User $owner = null, bool $isProtected = false): ?Email
    {
        $username = self::normalizeUsername($username);
        $domain = strtolower(trim($domain));

        if (! self::isValidUsername($username)) {
            return null;
        }
        if (! in_array($domain, Domain::activeDomainNames(), true)) {
            return null;
        }
        if (in_array($username, self::reservedUsernames(), true)) {
            return null;
        }

        $address = $username.'@'.$domain;

        if (Email::withTrashed()->where('email', $address)->exists()) {
            return null;
        }

        $email = new Email([
            'username' => $username,
            'domain' => $domain,
            'email' => $address,
            'status' => Email::STATUS_ACTIVE,
            'is_protected' => $isProtected,
        ]);

        if ($owner) {
            $email->user_id = $owner->id;
        }

        $email->save();
        Stat::storeEmailsCreated(1);

        return $email;
    }

    /**
     * Create N random emails distributed across the given active domains.
     *
     * @param  array<int,string>  $domains  active domain names; empty = all active
     * @return Collection<int,Email>
     */
    public static function createRandomBulk(int $qty, array $domains = [], ?User $owner = null, bool $isProtected = false): Collection
    {
        $qty = max(1, min(500, $qty));
        $allowed = self::filterActiveDomains($domains);

        if (empty($allowed)) {
            return collect();
        }

        $created = collect();
        $tries = 0;
        $maxTries = $qty * 5;

        DB::transaction(function () use (&$created, $qty, $allowed, $owner, $isProtected, &$tries, $maxTries) {
            while ($created->count() < $qty && $tries < $maxTries) {
                $tries++;
                $username = self::generatePronounceableUsername();
                $domain = $allowed[array_rand($allowed)];

                $email = self::createOne($username, $domain, $owner, $isProtected);
                if ($email) {
                    $created->push($email);
                }
            }
        });

        return $created;
    }

    /**
     * Create emails from a list of usernames and a list of allowed domains.
     * Each username is paired with a domain (round-robin if multiple domains).
     *
     * @param  array<int,string>  $usernames
     * @param  array<int,string>  $domains  active domain names; empty = all active
     * @return array{created: Collection<int,Email>, skipped: array<int,array{username:string,reason:string}>}
     */
    public static function createManualBulk(array $usernames, array $domains = [], ?User $owner = null, bool $isProtected = false): array
    {
        $allowed = self::filterActiveDomains($domains);
        $created = collect();
        $skipped = [];

        if (empty($allowed)) {
            foreach ($usernames as $username) {
                $skipped[] = ['username' => (string) $username, 'reason' => 'no-active-domain'];
            }

            return ['created' => $created, 'skipped' => $skipped];
        }

        $i = 0;
        DB::transaction(function () use (&$created, &$skipped, $usernames, $allowed, $owner, $isProtected, &$i) {
            foreach ($usernames as $raw) {
                $username = self::normalizeUsername((string) $raw);
                if (! self::isValidUsername($username)) {
                    $skipped[] = ['username' => (string) $raw, 'reason' => 'invalid'];

                    continue;
                }
                if (in_array($username, self::reservedUsernames(), true)) {
                    $skipped[] = ['username' => (string) $raw, 'reason' => 'reserved'];

                    continue;
                }

                $domain = $allowed[$i++ % count($allowed)];
                $address = $username.'@'.$domain;

                if (Email::withTrashed()->where('email', $address)->exists()) {
                    $skipped[] = ['username' => (string) $raw, 'reason' => 'duplicate'];

                    continue;
                }

                $email = self::createOne($username, $domain, $owner, $isProtected);
                if ($email) {
                    $created->push($email);
                } else {
                    $skipped[] = ['username' => (string) $raw, 'reason' => 'rejected'];
                }
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    public static function softDelete(Email $email): void
    {
        $email->delete();
    }

    public static function restore(Email $email): void
    {
        if ($email->trashed()) {
            $email->restore();
        }
        $email->forceFill(['status' => Email::STATUS_ACTIVE])->save();
    }

    public static function disable(Email $email): void
    {
        $email->forceFill(['status' => Email::STATUS_DISABLED])->save();
    }

    public static function reservedUsernames(): array
    {
        return array_unique(array_merge(
            self::RESERVED_USERNAMES,
            (array) config('app.settings.forbidden_ids', []),
        ));
    }

    /**
     * Lowercase + strip non-alphanumeric (but keep dot, dash, underscore, plus).
     */
    public static function normalizeUsername(string $username): string
    {
        $username = strtolower(trim($username));

        return preg_replace('/[^a-z0-9._\-+]/', '', $username) ?? '';
    }

    public static function isValidUsername(string $username): bool
    {
        if ($username === '') {
            return false;
        }
        $min = max(1, (int) config('app.settings.custom.min', 3));
        $max = min(64, (int) config('app.settings.custom.max', 32));
        $len = strlen($username);
        if ($len < $min || $len > $max) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9]([a-z0-9._\-+]*[a-z0-9])?$/', $username);
    }

    public static function isValidEmail(string $email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (! str_contains($email, '@')) {
            return false;
        }
        [$user, $domain] = explode('@', strtolower($email), 2);
        if (! self::isValidUsername($user)) {
            return false;
        }

        return preg_match('/^[a-z0-9]([a-z0-9.\-]*[a-z0-9])?\.[a-z]{2,}$/', $domain) === 1;
    }

    /**
     * Look up an active, non-deleted email row by address.
     */
    public static function findActive(string $email): ?Email
    {
        $email = strtolower(trim($email));
        if (! self::isValidEmail($email)) {
            return null;
        }

        return Email::active()->where('email', $email)->first();
    }

    /**
     * @param  array<int,string>  $domains
     * @return array<int,string>
     */
    private static function filterActiveDomains(array $domains): array
    {
        $active = Domain::activeDomainNames();
        if (empty($domains)) {
            return $active;
        }
        $domains = array_map(fn ($d) => strtolower(trim((string) $d)), $domains);

        return array_values(array_intersect($active, $domains));
    }

    private static function generatePronounceableUsername(): string
    {
        $c = 'bcdfghjklmnprstvwz';
        $v = 'aeiou';
        $a = $c.$v;
        $random = '';

        for ($j = 0; $j < 2; $j++) {
            $random .= $c[random_int(0, strlen($c) - 1)];
            $random .= $v[random_int(0, strlen($v) - 1)];
            $random .= $a[random_int(0, strlen($a) - 1)];
        }

        $start = (int) config('app.settings.random.start', 0);
        $end = (int) config('app.settings.random.end', 0);

        if ($start > 0 && $end >= $start) {
            $length = random_int($start, $end);
            $random = strtolower(Str::random($length));
            $random = preg_replace('/[^a-z0-9]/', 'x', $random);
        }

        return $random;
    }
}
