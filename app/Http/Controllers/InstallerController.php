<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PDO;
use Throwable;

class InstallerController extends Controller
{
    private array $minPhpVersion = ['8.2.0', '8.2'];

    private array $requirements = [
        'php' => [
            'version' => '8.2.0',
            'bcmath',
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'filter',
            'hash',
            'mbstring',
            'openssl',
            'pcre',
            'pdo',
            'pdo_mysql',
            'session',
            'tokenizer',
            'xml',
        ],
    ];

    private array $permissions = [
        'storage/framework/' => '775',
        'storage/framework/cache/' => '775',
        'storage/framework/sessions/' => '775',
        'storage/framework/views/' => '775',
        'storage/logs/' => '775',
        'bootstrap/cache/' => '775',
    ];

    public function welcome()
    {
        return view('installer.welcome');
    }

    public function requirements()
    {
        $results = [
            'php' => [
                'current' => PHP_VERSION,
                'minimum' => $this->requirements['php']['version'],
                'supported' => version_compare(PHP_VERSION, $this->requirements['php']['version'], '>='),
            ],
            'extensions' => [],
        ];

        $allSupported = $results['php']['supported'];

        foreach ($this->requirements['php'] as $key => $ext) {
            if ($key === 'version') continue;
            $loaded = extension_loaded($ext);
            $results['extensions'][$ext] = $loaded;
            if (!$loaded) {
                $allSupported = false;
            }
        }

        return view('installer.requirements', compact('results', 'allSupported'));
    }

    public function permissions()
    {
        $results = [];
        $allWritable = true;

        foreach ($this->permissions as $folder => $perm) {
            $path = base_path($folder);
            $isWritable = is_dir($path) && is_writable($path);
            $results[$folder] = [
                'is_writable' => $isWritable,
                'path' => $path,
            ];
            if (!$isWritable) {
                $allWritable = false;
            }
        }

        // Check .env writable
        $envPath = base_path('.env');
        $envWritable = file_exists($envPath) ? is_writable($envPath) : is_writable(base_path());
        $results['.env'] = [
            'is_writable' => $envWritable,
            'path' => $envPath,
        ];
        if (!$envWritable) {
            $allWritable = false;
        }

        return view('installer.permissions', compact('results', 'allWritable'));
    }

    public function environment()
    {
        $env = [
            'app_name' => env('APP_NAME', 'Doc Helper AI'),
            'app_url' => env('APP_URL', request()->root()),
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_name' => env('DB_DATABASE', 'doc_helper_db'),
            'db_user' => env('DB_USERNAME', 'root'),
            'db_password' => env('DB_PASSWORD', ''),
        ];

        return view('installer.environment', compact('env'));
    }

    public function saveEnvironment(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_url' => 'required|url',
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // 1. Test MySQL Connection using raw PDO
        try {
            $dbHost = $validated['db_host'];
            $dbPort = $validated['db_port'];
            $dbName = $validated['db_name'];
            $dbUser = $validated['db_user'];
            $dbPass = $validated['db_password'] ?? '';

            $pdo = new PDO("mysql:host={$dbHost};port={$dbPort}", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            // Try creating DB if it does not exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database connection failed: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withInput()->withErrors([
                'db_error' => 'Database connection failed: ' . $e->getMessage(),
            ]);
        }

        // 2. Write to .env
        $this->updateEnvFile([
            'APP_NAME' => '"' . $validated['app_name'] . '"',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => $validated['app_url'],
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $validated['db_host'],
            'DB_PORT' => $validated['db_port'],
            'DB_DATABASE' => $validated['db_name'],
            'DB_USERNAME' => $validated['db_user'],
            'DB_PASSWORD' => $validated['db_password'] ?? '',
        ]);

        // Clear cached config so newly written DB settings take effect
        try {
            Artisan::call('config:clear');
        } catch (Throwable $e) {}

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('installer.database'),
            ]);
        }

        return redirect()->route('installer.database');
    }

    public function database()
    {
        return view('installer.database');
    }

    public function runDatabase()
    {
        try {
            // Generate App Key if missing
            if (empty(config('app.key')) || config('app.key') === 'base64:...') {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // Run Migrations
            Artisan::call('migrate:fresh', ['--force' => true]);

            // Run Seeders
            Artisan::call('db:seed', ['--force' => true]);

            // Storage Link
            try {
                Artisan::call('storage:link');
            } catch (Throwable $e) {}

            return redirect()->route('installer.admin');
        } catch (Throwable $e) {
            return back()->withErrors([
                'migration_error' => 'Database migration/seeding failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function admin()
    {
        return view('installer.admin');
    }

    public function saveAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Find existing admin or create a new Super Admin
            $user = User::where('email', $validated['email'])->first();
            if ($user) {
                $user->update([
                    'name' => $validated['name'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'super_admin',
                    'email_verified_at' => now(),
                ]);
            } else {
                User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'super_admin',
                    'email_verified_at' => now(),
                ]);
            }

            return redirect()->route('installer.finish');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors([
                'admin_error' => 'Failed to create Super Admin user: ' . $e->getMessage(),
            ]);
        }
    }

    public function finish()
    {
        // 1. Create storage/installed timestamp file
        try {
            file_put_contents(storage_path('installed'), now()->toDateTimeString());
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
        } catch (Throwable $e) {}

        return view('installer.finish');
    }

    /**
     * Update .env file with new key-value pairs safely.
     */
    private function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                touch($envPath);
            }
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $content);
    }
}