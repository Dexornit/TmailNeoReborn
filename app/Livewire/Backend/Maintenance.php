<?php

namespace App\Livewire\Backend;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * Admin maintenance dashboard.
 *
 * Lets admins run migrate, clear cache, recreate storage:link, and view
 * health-check results without ever needing terminal access. This is
 * critical for shared-hosting deploys where SSH/Composer/npm aren't
 * available.
 */
class Maintenance extends Component {

    public array $checks = [];
    public string $message = '';
    public string $error = '';

    public function mount() {
        $this->checks = $this->runHealthChecks();
    }

    public function migrate() {
        $this->reset(['message', 'error']);
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->message = 'Migrations executed. ' . trim(Artisan::output());
        } catch (\Throwable $e) {
            Log::error('Maintenance migrate failed: ' . $e->getMessage());
            $this->error = $e->getMessage();
        }
        $this->checks = $this->runHealthChecks();
    }

    public function clearCache() {
        $this->reset(['message', 'error']);
        try {
            Artisan::call('optimize:clear');
            $this->message = 'Caches cleared. ' . trim(Artisan::output());
        } catch (\Throwable $e) {
            Log::error('Maintenance optimize:clear failed: ' . $e->getMessage());
            $this->error = $e->getMessage();
        }
        $this->checks = $this->runHealthChecks();
    }

    public function storageLink() {
        $this->reset(['message', 'error']);
        try {
            Artisan::call('storage:link', ['--force' => true]);
            $this->message = 'Storage symlink recreated. ' . trim(Artisan::output());
        } catch (\Throwable $e) {
            Log::error('Maintenance storage:link failed: ' . $e->getMessage());
            $this->error = $e->getMessage();
        }
        $this->checks = $this->runHealthChecks();
    }

    public function refreshChecks() {
        $this->reset(['message', 'error']);
        $this->checks = $this->runHealthChecks();
        $this->message = 'Checks refreshed.';
    }

    private function runHealthChecks(): array {
        $checks = [];

        try {
            $count = DB::table('migrations')->count();
            $checks[] = [
                'name' => 'Database migrations applied',
                'ok' => $count > 0,
                'detail' => $count > 0 ? $count . ' migrations recorded' : 'migrations table empty'
            ];
        } catch (\Throwable $e) {
            $checks[] = ['name' => 'Database migrations applied', 'ok' => false, 'detail' => $e->getMessage()];
        }

        $storageDirs = [
            storage_path('logs'),
            storage_path('framework'),
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];
        $unwritable = array_filter($storageDirs, fn($d) => !is_writable($d));
        $checks[] = [
            'name' => 'Storage directories writable',
            'ok' => empty($unwritable),
            'detail' => empty($unwritable) ? 'All writable' : 'Not writable: ' . implode(', ', $unwritable)
        ];

        $manifest = public_path('build/manifest.json');
        $checks[] = [
            'name' => 'Front-end assets present (public/build)',
            'ok' => is_readable($manifest),
            'detail' => is_readable($manifest) ? 'manifest.json readable' : 'Missing ' . $manifest
        ];

        $publicStorage = public_path('storage');
        $checks[] = [
            'name' => 'public/storage symlink',
            'ok' => file_exists($publicStorage),
            'detail' => file_exists($publicStorage) ? 'symlink resolved' : 'symlink missing'
        ];

        $envFile = base_path('.env');
        $checks[] = [
            'name' => '.env file present',
            'ok' => is_readable($envFile),
            'detail' => is_readable($envFile) ? '.env readable' : 'Missing or unreadable'
        ];

        $checks[] = [
            'name' => 'Install flag file',
            'ok' => is_readable(storage_path('installed')),
            'detail' => storage_path('installed')
        ];

        return $checks;
    }

    public function render() {
        return view('backend.maintenance');
    }
}
