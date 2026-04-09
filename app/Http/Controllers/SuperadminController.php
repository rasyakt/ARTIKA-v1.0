<?php

namespace App\Http\Controllers;

use App\Helpers\ThemeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SuperadminController extends Controller
{
    /**
     * Display the developer tools dashboard.
     */
    public function index()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_os' => PHP_OS_FAMILY,
            'db_connection' => config('database.default'),
            'db_version' => $this->getDbVersion(),
            'server_time' => now()->toDateTimeString(),
            'is_maintenance' => app()->isDownForMaintenance(),
            'environment' => app()->environment(),
        ];

        $dbStats = $this->getDatabaseStats();

        return view('superadmin.dashboard', compact('systemInfo', 'dbStats'));
    }

    /**
     * Get list of tables and record counts.
     */
    private function getDatabaseStats()
    {
        $stats = [];
        try {
            $driver = config('database.default');
            $tables = [];

            if ($driver === 'mysql') {
                $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            } elseif ($driver === 'pgsql') {
                $tables = \Illuminate\Support\Facades\DB::select("SELECT tablename as table_name FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
            }

            foreach ($tables as $table) {
                // Get the first property value from the stdClass object (the table name)
                $tableArray = (array) $table;
                $tableName = reset($tableArray);

                if ($tableName) {
                    $count = \Illuminate\Support\Facades\DB::table($tableName)->count();
                    $stats[] = [
                        'name' => $tableName,
                        'count' => $count
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback for non-MySQL or errors
        }
        return $stats;
    }

    /**
     * Toggle Maintenance Mode.
     */
    public function toggleMaintenance(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');

            // Clear the bypass cookies
            return back()
                ->withCookie(cookie()->forget('laravel_maintenance'))
                ->with('success', 'Application is now LIVE.');
        } else {
            // Verify superadmin password before enabling maintenance mode
            $password = $request->input('password');
            if (!$password || !\Illuminate\Support\Facades\Hash::check($password, $request->user()->password)) {
                return back()->with('error', 'Password salah. Maintenance Mode tidak diaktifkan.');
            }

            // Internal secret token (used for cookie HMAC only, never exposed as URL)
            $token = bin2hex(random_bytes(16));
            Artisan::call('down', [
                '--secret' => $token
            ]);

            \Illuminate\Support\Facades\Log::warning("Maintenance Mode enabled by Superadmin ID: " . $request->user()->id);

            // Set the bypass cookie DIRECTLY on this browser — no shareable URL.
            // Only this specific browser session gets maintenance bypass access.
            $bypassCookie = \Illuminate\Foundation\Http\MaintenanceModeBypassCookie::create($token);

            return back()
                ->withCookie($bypassCookie)
                ->with('success', 'Maintenance Mode berhasil diaktifkan. Hanya browser ini yang dapat mengakses website.');
        }
    }

    /**
     * Verify Superadmin Password via AJAX.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if (\Illuminate\Support\Facades\Hash::check($request->input('password'), $request->user()->password)) {
            return response()->json(['valid' => true]);
        }

        return response()->json(['valid' => false, 'message' => 'Password yang Anda masukkan salah.'], 422);
    }

    /**
     * Get Database Version.
     */
    private function getDbVersion()
    {
        try {
            $driver = config('database.default');

            if ($driver === 'mysql') {
                $results = \Illuminate\Support\Facades\DB::select('SELECT VERSION() as version');
            } elseif ($driver === 'pgsql') {
                $results = \Illuminate\Support\Facades\DB::select('SELECT version()');
            } elseif ($driver === 'sqlite') {
                $results = \Illuminate\Support\Facades\DB::select('SELECT sqlite_version() as version');
            } else {
                return 'Driver: ' . $driver;
            }

            $resultsArray = (array) ($results[0] ?? []);
            return reset($resultsArray) ?: 'Unknown';
        } catch (\Exception $e) {
            return 'Error: ' . substr($e->getMessage(), 0, 30);
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        // optimize:clear is more thorough as it clears everything in one go
        Artisan::call('optimize:clear');

        return back()->with('success', 'All system caches have been cleared successfully!');
    }

    /**
     * Run system optimization.
     */
    public function optimize()
    {
        Artisan::call('optimize');

        return back()->with('success', 'System optimized successfully!');
    }

    /**
     * Display system logs.
     */
    public function logs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = '';

        if (File::exists($logPath)) {
            // Get last 1000 lines to have enough potential error candidates
            $file = new \SplFileObject($logPath, 'r');
            $file->seek(PHP_INT_MAX);
            $lastLine = $file->key();
            $lines = new \LimitIterator($file, max(0, $lastLine - 1000), $lastLine);

            $errorKeywords = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
            $filteredLogs = [];

            foreach ($lines as $line) {
                foreach ($errorKeywords as $keyword) {
                    if (stripos($line, '.' . $keyword) !== false || stripos($line, 'local.' . $keyword) !== false || stripos($line, 'production.' . $keyword) !== false) {
                        $filteredLogs[] = $line;
                        break;
                    }
                }
            }

            $logs = implode("", array_slice($filteredLogs, -500)); // Show last 500 filtered errors

            if (empty($logs)) {
                $logs = "No system errors found in the last 1000 log entries.";
            }
        } else {
            $logs = 'Log file not found.';
        }

        return view('superadmin.logs', compact('logs'));
    }

    /**
     * Display advanced system settings.
     */
    public function settings()
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');

        // Define default settings categories
        $categories = [
            'General' => [
                'system_name' => ['label' => 'Nama Aplikasi', 'type' => 'text', 'default' => 'ARTIKA POS'],
                'store_name' => ['label' => 'Nama Toko (di Struk/PDF)', 'type' => 'text', 'default' => 'ARTIKA POS'],
                'store_phone' => ['label' => 'No. Telepon Toko', 'type' => 'text', 'default' => ''],
                'store_email' => ['label' => 'Email Toko', 'type' => 'text', 'default' => ''],
                'store_address' => ['label' => 'Alamat Toko', 'type' => 'text', 'default' => ''],
                'site_logo_login' => ['label' => 'Logo Halaman Login', 'type' => 'file', 'accept' => '.png,.jpg,.jpeg,.webp', 'default' => 'img/logo.png'],
                'site_logo' => ['label' => 'Logo Aplikasi (Navbar & Lainnya)', 'type' => 'file', 'accept' => '.png,.jpg,.jpeg,.webp', 'default' => 'img/logo2.png'],
                'login_background' => ['label' => 'Background Halaman Login', 'type' => 'file', 'accept' => '.png,.jpg,.jpeg,.webp', 'default' => ''],
                'site_color_theme' => ['label' => 'Tema Warna Website', 'type' => 'palette', 'default' => 'brown'],
                'custom_primary_color' => ['label' => 'Warna Kustom (Primary)', 'type' => 'color', 'default' => '#85695a'],
                'enable_faq' => ['label' => 'Aktifkan Fitur Pusat Bantuan (FAQ)', 'type' => 'boolean', 'default' => true],
            ],
            'POS & Struk' => [
                'invoice_prefix' => ['label' => 'Prefix No. Struk', 'type' => 'text', 'default' => 'INV'],
                'invoice_format' => [
                    'label' => 'Format No. Struk',
                    'type' => 'select',
                    'default' => '{PREFIX}-{RAND}',
                    'options' => [
                        '{PREFIX}-{RAND}' => 'PREFIX-RANDOM  (INV-A8K2M3N1P9)',
                        '{PREFIX}-{DATE}-{SEQ}' => 'PREFIX-TANGGAL-URUT  (INV-20260220-00042)',
                        '{PREFIX}/{DATE}/{RAND}' => 'PREFIX/TANGGAL/RANDOM  (INV/20260220/X7K3M2)',
                        '{PREFIX}-{DATE}-{RAND}' => 'PREFIX-TANGGAL-RANDOM  (INV-20260220-A8K2M3)',
                    ]
                ],
                'invoice_rand_length' => ['label' => 'Panjang Kode Acak', 'type' => 'number', 'default' => 10],
                'invoice_seq_padding' => ['label' => 'Digit Nomor Urut', 'type' => 'number', 'default' => 5],
                'receipt_paper_size' => [
                    'label' => 'Ukuran Kertas Struk',
                    'type' => 'select',
                    'default' => '58mm',
                    'options' => ['58mm' => '58mm (Thermal Kecil)', '80mm' => '80mm (Thermal Besar)']
                ],
            ],
            'Admin Features' => [
                'admin_enable_audit_logs' => ['label' => 'Audit Logs', 'type' => 'boolean', 'default' => true],
                'admin_enable_reports' => ['label' => 'Detailed Reports', 'type' => 'boolean', 'default' => true],
                'admin_enable_camera' => ['label' => 'Feature Toggles (Scanner)', 'type' => 'boolean', 'default' => true],
                'admin_enable_promos' => ['label' => 'Promos & Discounts Management', 'type' => 'boolean', 'default' => true],
            ],
            'Warehouse Features' => [
                'warehouse_enable_adjust' => ['label' => 'Stock Adjustment', 'type' => 'boolean', 'default' => true],
                'warehouse_enable_scrap' => ['label' => 'Scrap/Delete Batches', 'type' => 'boolean', 'default' => true],
            ],
            'Cashier Features' => [
                'cashier_enable_returns' => ['label' => 'Returns & Refunds', 'type' => 'boolean', 'default' => true],
                'cashier_enable_discounts' => ['label' => 'Manual Discounts', 'type' => 'boolean', 'default' => true],
                'cashier_enable_camera' => ['label' => 'Camera Scanner', 'type' => 'boolean', 'default' => true],
                'cashier_enable_product_photos' => ['label' => 'Tampilkan Foto Produk', 'type' => 'boolean', 'default' => true],
                'cashier_enable_audit_logs' => ['label' => 'Activity Logs', 'type' => 'boolean', 'default' => true],
            ],
            'Sustainability & Performance' => [
                'session_duration' => ['label' => 'Session Duration (Minutes)', 'type' => 'number', 'default' => 120],

            ],
            'Image & Storage' => [
                'image_max_width' => ['label' => 'Max Lebar Gambar (px)', 'type' => 'number', 'default' => 1920],
                'image_quality' => ['label' => 'Kualitas Kompresi (%)', 'type' => 'number', 'default' => 80],
                'image_format' => [
                    'label' => 'Format Output Gambar',
                    'type' => 'select',
                    'default' => 'webp',
                    'options' => [
                        'webp' => 'WebP (Terkecil, Modern)',
                        'jpg' => 'JPEG (Universal)',
                        'png' => 'PNG (Tanpa Kompresi Lossy)',
                    ]
                ],
            ],
        ];

        $palettePreviews = ThemeHelper::getPalettePreviewColors();

        return view('superadmin.settings.index', compact('settings', 'categories', 'palettePreviews'));
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request)
    {
        // Handle file uploads first
        $fileKeys = ['site_logo_login', 'site_logo', 'login_background'];
        foreach ($fileKeys as $fileKey) {
            // Handle file removal
            if ($request->has('remove_' . $fileKey)) {
                $oldPath = \App\Models\Setting::get($fileKey);
                if ($oldPath && $oldPath !== 'img/logo2.png' && file_exists(public_path($oldPath))) {
                    @unlink(public_path($oldPath));
                }
                \App\Models\Setting::set($fileKey, '');
                continue;
            }

            // Handle file upload
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);

                // Validate
                $request->validate([
                    $fileKey => 'image|mimes:png,jpg,jpeg,webp|max:2048',
                ]);

                // Delete old file if exists
                $oldPath = \App\Models\Setting::get($fileKey);
                if ($oldPath && $oldPath !== 'img/logo2.png' && file_exists(public_path($oldPath))) {
                    @unlink(public_path($oldPath));
                }

                // Store new file
                $filename = $fileKey . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img/uploads'), $filename);
                \App\Models\Setting::set($fileKey, 'img/uploads/' . $filename);
            }
        }

        $settings = $request->except(array_merge(['_token'], $fileKeys, array_map(fn($k) => 'remove_' . $k, $fileKeys)));

        foreach ($settings as $key => $value) {
            // Skip file inputs that didn't have a file
            if (in_array($key, $fileKeys))
                continue;
            // Convert 'on' from checkboxes to boolean strings
            if ($value === 'on')
                $value = 'true';
            \App\Models\Setting::set($key, $value);
        }

        // Handle unchecked checkboxes (they won't be in the request)
        $allKeys = [
            'admin_enable_audit_logs',
            'admin_enable_reports',
            'admin_enable_camera',
            'admin_enable_promos',
            'warehouse_enable_adjust',
            'warehouse_enable_scrap',
            'cashier_enable_returns',
            'cashier_enable_discounts',
            'cashier_enable_camera',
            'cashier_enable_product_photos',
            'cashier_enable_audit_logs',

            'enable_faq'
        ];

        foreach ($allKeys as $key) {
            if (!$request->has($key)) {
                \App\Models\Setting::set($key, 'false');
            }
        }

        return back()->with('success', 'System settings updated successfully!');
    }
}
