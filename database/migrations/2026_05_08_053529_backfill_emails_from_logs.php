<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('emails') || ! Schema::hasTable('logs')) {
            return;
        }

        $existing = DB::table('emails')->pluck('email')->all();
        $existingMap = array_flip($existing);

        DB::table('logs')
            ->select('email', DB::raw('MAX(created_at) as last_used'))
            ->groupBy('email')
            ->orderBy('email')
            ->chunk(500, function ($rows) use (&$existingMap) {
                $now = now();
                $batch = [];

                foreach ($rows as $row) {
                    if (! $row->email || ! str_contains($row->email, '@')) {
                        continue;
                    }
                    if (isset($existingMap[$row->email])) {
                        continue;
                    }

                    [$username, $domain] = explode('@', $row->email, 2);
                    $username = strtolower(trim($username));
                    $domain = strtolower(trim($domain));

                    if ($username === '' || $domain === '') {
                        continue;
                    }

                    $batch[] = [
                        'username' => $username,
                        'domain' => $domain,
                        'email' => $row->email,
                        'user_id' => null,
                        'status' => 'active',
                        'is_protected' => false,
                        'last_used_at' => $row->last_used,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $existingMap[$row->email] = true;
                }

                if (! empty($batch)) {
                    DB::table('emails')->insertOrIgnore($batch);
                }
            });
    }

    public function down(): void
    {
        // No-op: keeping backfilled data is desirable.
    }
};
