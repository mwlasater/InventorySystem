<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'settings' => [
                'app_display_name' => Setting::get('app_display_name'),
                'items_per_page' => Setting::get('items_per_page'),
                'trash_retention_days' => Setting::get('trash_retention_days'),
                'enforce_2fa_for_admins' => Setting::get('enforce_2fa_for_admins'),
            ],
            'system' => $this->systemInfo(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_display_name' => 'required|string|max:100',
            'items_per_page' => 'required|integer|in:25,50,100',
            'trash_retention_days' => 'required|integer|min:1|max:3650',
            'enforce_2fa_for_admins' => 'sometimes|boolean',
        ]);

        Setting::set('app_display_name', $validated['app_display_name']);
        Setting::set('items_per_page', $validated['items_per_page']);
        Setting::set('trash_retention_days', $validated['trash_retention_days']);
        Setting::set('enforce_2fa_for_admins', $request->boolean('enforce_2fa_for_admins'));

        AuditLog::record('update', 'settings', null, null, $validated);

        return back()->with('success', 'Settings saved.');
    }

    /**
     * Read-only operational overview.
     *
     * @return array<string, mixed>
     */
    private function systemInfo(): array
    {
        $backupDir = storage_path('app/backups');
        $backups = is_dir($backupDir) ? glob("{$backupDir}/backup-*") : [];
        $latestBackup = ! empty($backups)
            ? collect($backups)->max(fn ($f) => filemtime($f))
            : null;

        $mailMailer = config('mail.default');

        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'debug' => config('app.debug'),
            'database' => config('database.default'),
            'queue' => config('queue.default'),
            'mail_configured' => $mailMailer !== 'log' && $mailMailer !== 'array',
            'mail_mailer' => $mailMailer,
            'item_count' => Item::active()->count(),
            'user_count' => User::count(),
            'backup_count' => count($backups),
            'latest_backup_at' => $latestBackup ? date('Y-m-d H:i', $latestBackup) : null,
        ];
    }
}
