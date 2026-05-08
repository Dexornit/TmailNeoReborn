<?php

namespace App\Livewire\Installer;

use App\Models\Domain;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\Setting;
use App\Models\User;
use App\Services\TMail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Installer extends Component {

    public $state = [
        'app_name' => 'TMail',
        'db' => [
            'host' => 'localhost',
            'port' => 3306,
            'connection' => 'mysql',
            'database' => '',
            'username' => '',
            'password' => ''
        ],
        'engine' => '',
        'domains' => [],
        'imap' => [
            'host' => '',
            'port' => 993,
            'encryption' => '',
            'validate_cert' => false,
            'username' => '',
            'password' => '',
            'default_account' => 'default',
            'protocol' => 'imap'
        ],
        'admin' => [
            'name' => '',
            'email' => '',
            'password' => ''
        ],
        'license_key' => ''
    ];
    public $current = 0;
    public $error = '';
    public $success = '';
    public $healthChecks = [];

    protected $listeners = ['runMigrations'];

    public function mount() {
        // Make sure Laravel's expected runtime directories exist before we do
        // anything else. On shared hosting these often go missing after a zip
        // upload because empty dirs aren't preserved.
        self::ensureRuntimeDirectories();
        $this->state['db'] = [
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'connection' => env('DB_CONNECTION'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD')
        ];
    }

    /**
     * Recreate Laravel runtime directories that are required for the app
     * to boot at all (storage/framework/views, bootstrap/cache, etc.).
     *
     * Static so it can also be called from a tiny bootstrap script when the
     * full app cannot boot because of a missing dir.
     */
    public static function ensureRuntimeDirectories(): array {
        $dirs = [
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('framework/views'),
            storage_path('logs'),
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/private'),
            base_path('bootstrap/cache'),
        ];
        $created = [];
        foreach ($dirs as $d) {
            if (!is_dir($d)) {
                if (@mkdir($d, 0775, true)) {
                    $created[] = $d;
                }
            }
            @chmod($d, 0775);
        }
        return $created;
    }

    public function add($type = 'domains') {
        $this->resetErrorBag();
        array_push($this->state[$type], '');
    }

    public function remove($type = 'domains', $key = '') {
        unset($this->state[$type][$key]);
    }

    public function save() {
        $this->error = '';
        $this->success = '';
        if ($this->current === 0) {
            $this->validate(
                [
                    'state.db.host' => 'required',
                    'state.db.port' => 'required|numeric',
                    'state.db.connection' => 'required',
                    'state.db.database' => 'required',
                    'state.db.username' => 'required',
                    'state.db.password' => 'required',
                ],
                [
                    'state.db.host.required' => 'Host field is Required',
                    'state.db.port.required' => 'Port field is Required',
                    'state.db.port.numeric' => 'Port field can only be Numeric',
                    'state.db.connection.required' => 'Connection field is Required',
                    'state.db.database.required' => 'Database field is Required',
                    'state.db.username.required' => 'Username field is Required',
                    'state.db.password.required' => 'Password field is Required',
                ]
            );
            /**
             * Below function will call a Browser Event which will eventually call the Livewire Function to run Migrations
             * 
             * This is because when .env file is changed, it still cached until a response is sent back to the client
             */
            $this->db();
        } else if ($this->current === 1) {
            $this->validate(
                [
                    'state.license_key' => 'required',
                    'state.app_name' => 'required',
                ],
                [
                    'state.license_key.required' => 'License Key is Required',
                    'state.app_name.required' => 'App Name is Required',
                ]
            );
            if ($this->checkLicense()) {
                $this->current = 2;
                Setting::put('name', $this->state['app_name']);
                Setting::put('license_key', $this->state['license_key']);
            }
        } else if ($this->current === 2) {
            $this->validate(
                [
                    'state.domains.0' => 'required',
                    'state.domains.*' => 'required',
                    'state.engine' => 'required'
                ],
                [
                    'state.domains.0.required' => 'Atleast one Domain is Required',
                    'state.domains.*.required' => 'Domain field is Required',
                    'state.engine.required' => 'Select a Engine for your TMail'
                ]
            );
            foreach ($this->state['domains'] as $domain) {
                Domain::create([
                    'domain' => $domain,
                ]);
            }
            Setting::put('engine', $this->state['engine']);
            if ($this->state['engine'] == 'imap') {
                $this->validate(
                    [
                        'state.imap.host' => 'required',
                        'state.imap.port' => 'required|numeric',
                        'state.imap.username' => 'required',
                        'state.imap.password' => 'required',
                    ],
                    [
                        'state.imap.host.required' => 'Host field is Required',
                        'state.imap.port.required' => 'Port field is Required',
                        'state.imap.port.numeric' => 'Port field can only be Numeric',
                        'state.imap.username.required' => 'Username field is Required',
                        'state.imap.password.required' => 'Password field is Required',
                    ]
                );
                if ($this->test()) {
                    Setting::put('imap', $this->state['imap']);
                    $this->success = 'IMAP Connection Successfully Established. Please proceed on creating a Admin Account.';
                    $this->current = 3;
                }
            } else if ($this->state['engine'] == 'delivery') {
                $this->success = 'Saved Successfully. Please proceed on creating a Admin Account.';
                $this->current = 3;
            }
        } else {
            $this->validate(
                [
                    'state.admin.name' => 'required',
                    'state.admin.email' => 'required',
                    'state.admin.password' => 'required',
                ],
                [
                    'state.admin.name.required' => 'Admin Name is Required',
                    'state.admin.email.required' => 'Email ID is Required',
                    'state.admin.password.required' => 'Password for Admin is Required',
                ]
            );
            if ($this->createAdminAccount()) {
                $this->changeSessionDriver();
                $this->finalizeInstallation();
                file_put_contents(storage_path('installed'), 'TMail successfully installed on ' . date('Y/m/d h:i:sa'));
                $this->healthChecks = $this->runHealthChecks();
                $this->success = 'Installation Completed Successfully!';
                $this->current = 4;
            }
        }
    }

    /**
     * Run final post-install Artisan commands so shared-hosting users
     * never have to touch a terminal. All commands are PHP-only via Artisan::call().
     */
    private function finalizeInstallation() {
        try {
            Artisan::call('storage:link', ['--force' => true]);
        } catch (\Throwable $e) {
            Log::warning('storage:link failed: ' . $e->getMessage());
        }
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            Log::warning('optimize:clear failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify the install actually worked. Each check returns ok|fail + a hint
     * so users can self-diagnose before they ever ssh in.
     */
    private function runHealthChecks(): array {
        $checks = [];

        // 1. Migrations applied
        try {
            $count = DB::table('migrations')->count();
            $checks[] = [
                'name' => 'Database migrations applied',
                'ok' => $count > 0,
                'detail' => $count > 0 ? $count . ' migrations recorded' : 'migrations table is empty'
            ];
        } catch (\Throwable $e) {
            $checks[] = ['name' => 'Database migrations applied', 'ok' => false, 'detail' => $e->getMessage()];
        }

        // 2. Installed flag file present
        $installedFlag = storage_path('installed');
        $checks[] = [
            'name' => 'Install flag file written',
            'ok' => is_readable($installedFlag),
            'detail' => $installedFlag
        ];

        // 3. Storage directories exist + writable
        $storageDirs = [
            storage_path('logs'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('app'),
            base_path('bootstrap/cache'),
        ];
        $missing = array_filter($storageDirs, fn($d) => !is_dir($d));
        $unwritable = array_filter($storageDirs, fn($d) => is_dir($d) && !is_writable($d));
        $checks[] = [
            'name' => 'Storage directories exist and writable',
            'ok' => empty($missing) && empty($unwritable),
            'detail' => (empty($missing) && empty($unwritable))
                ? 'storage/framework/{cache,sessions,views}, storage/{logs,app}, bootstrap/cache all OK'
                : trim(
                    (empty($missing) ? '' : 'Missing: ' . implode(', ', $missing) . '. ')
                    . (empty($unwritable) ? '' : 'Not writable: ' . implode(', ', $unwritable))
                )
        ];

        // 4. Pre-built front-end assets present (public/build/manifest.json)
        $manifest = public_path('build/manifest.json');
        $checks[] = [
            'name' => 'Front-end assets present (public/build)',
            'ok' => is_readable($manifest),
            'detail' => is_readable($manifest) ? 'manifest.json readable' : 'Missing ' . $manifest
        ];

        // 5. Public storage symlink resolved (best-effort, non-fatal)
        $publicStorage = public_path('storage');
        $checks[] = [
            'name' => 'public/storage symlink',
            'ok' => file_exists($publicStorage),
            'detail' => file_exists($publicStorage) ? 'symlink resolved' : 'symlink missing (re-run installer or click Maintenance)'
        ];

        // 6. Admin account created
        try {
            $admins = User::where('role', 7)->count();
            $checks[] = [
                'name' => 'Admin account created',
                'ok' => $admins > 0,
                'detail' => $admins . ' admin user(s)'
            ];
        } catch (\Throwable $e) {
            $checks[] = ['name' => 'Admin account created', 'ok' => false, 'detail' => $e->getMessage()];
        }

        return $checks;
    }

    public function runMigrations() {
        try {
            // Make sure storage/framework/views etc. exist before any artisan call.
            self::ensureRuntimeDirectories();

            // Clear config/cache so the .env values just written take effect
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
            if ($exitCode === 0) {
                // Clear views/route caches once schema is fresh
                try {
                    Artisan::call('optimize:clear');
                } catch (\Throwable $e) {
                    Log::warning('optimize:clear failed: ' . $e->getMessage());
                }
                $this->success = 'Database Connection Successful. Please proceed with further details.';
                $this->current = 1;
            } else {
                $this->error = 'Migration failed with exit code: ' . $exitCode . '. Output: ' . trim(Artisan::output());
            }
        } catch (\Exception $e) {
            Log::error('Migration error: ' . $e->getMessage());
            $this->error = 'Migration failed: ' . $e->getMessage();
        }
    }

    public function render() {
        return view('installer.installer');
    }

    /** Get URL of Website */
    private function getAppURL() {
        $url = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url .= "https://";
        } else {
            $url .= "http://";
        }
        $url .= $_SERVER['HTTP_HOST'];
        return $url;
    }

    /** License Key Check */
    private function checkLicense() {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('storage:link', ['--force' => true]);

            $this->success = 'License check bypassed. Please enter the IMAP Details.';
            
            $this->state['license_key'] = 'babiato-vilaseca-license-bypass';
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /** Create Admin Account */
    private function createAdminAccount() {
        return User::create([
            'name' => $this->state['admin']['name'],
            'email' => $this->state['admin']['email'],
            'password' => Hash::make($this->state['admin']['password']),
            'role' => 7
        ]);
    }

    /** Test IMAP Connection */
    private function test() {
        try {
            TMail::connectMailBox($this->state['imap']);
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    /** Save DB Details and Validate Connection */
    private function db() {
        try {
            $data = [
                'APP_NAME' => $this->state['app_name'],
                'APP_URL' => $this->getAppURL(),
                'DB_CONNECTION' => $this->state['db']['connection'],
                'DB_HOST' => $this->state['db']['host'],
                'DB_PORT' => $this->state['db']['port'],
                'DB_DATABASE' => $this->state['db']['database'],
                'DB_USERNAME' => $this->state['db']['username'],
                'DB_PASSWORD' => $this->state['db']['password'],
            ];
            $this->changeEnv($data);
            $this->dispatch('run-migrations');
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    private function changeSessionDriver() {
        try {
            $data = [
                'SESSION_DRIVER' => 'database',
            ];
            $this->changeEnv($data);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /** Save Details to env file */
    private function changeEnv($data = array()) {
        if (count($data) > 0) {
            $env = file_get_contents(base_path() . '/.env');
            $env = explode("\n", $env);
            foreach ((array)$data as $key => $value) {
                $notfound = true;
                foreach ($env as $env_key => $env_value) {
                    $entry = explode("=", $env_value, 2);
                    if ($entry[0] == $key) {
                        $env[$env_key] = $key . "=\"" . $value . "\"";
                        $notfound = false;
                    } else {
                        $env[$env_key] = $env_value;
                    }
                }
                if ($notfound) {
                    $env[$env_key + 1] = "\n" . $key . "=\"" . $value . "\"";
                }
            }
            $env = implode("\n", $env);
            file_put_contents(base_path() . '/.env', $env);
            return true;
        } else {
            return false;
        }
    }
}
