<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use ZipArchive;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:run {--with-media : Include uploaded photos and documents in the backup}';
    protected $description = 'Create a database backup with optional media files';

    public function handle(): int
    {
        $config = config('database.connections.mysql');
        $backupDir = storage_path('app/backups');
        $timestamp = now()->format('Y-m-d-His');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $sqlFilename = "backup-{$timestamp}.sql";
        $sqlPath = "{$backupDir}/{$sqlFilename}";

        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h%s -P%s -u%s %s %s > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            empty($config['password']) ? '' : '-p' . escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($sqlPath)
        );

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Database backup failed: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }

        $this->info("Database backup created: {$sqlFilename}");

        // Optionally include media files in a zip archive
        if ($this->option('with-media')) {
            $zipFilename = "backup-{$timestamp}.zip";
            $zipPath = "{$backupDir}/{$zipFilename}";

            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $this->error('Could not create zip archive.');
                return Command::FAILURE;
            }

            // Add the SQL dump to the archive
            $zip->addFile($sqlPath, $sqlFilename);

            // Add media files from storage/app/public
            $mediaPath = storage_path('app/public');
            if (is_dir($mediaPath)) {
                $this->addDirectoryToZip($zip, $mediaPath, 'media');
            }

            $zip->close();

            // Remove the standalone SQL file since it's now in the zip
            unlink($sqlPath);

            $this->info("Media archive created: {$zipFilename}");
        }

        // Rotate old backups: delete files older than 30 days
        $rotated = $this->rotateOldBackups($backupDir);
        $this->info("Backup rotation: {$rotated} old backup(s) deleted.");

        return Command::SUCCESS;
    }

    /**
     * Recursively add a directory to a ZipArchive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $prefix): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = $prefix . '/' . substr($file->getPathname(), strlen($directory) + 1);
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
    }

    /**
     * Delete backup files older than 30 days.
     */
    private function rotateOldBackups(string $backupDir): int
    {
        $count = 0;
        $cutoff = now()->subDays(30)->timestamp;

        foreach (glob("{$backupDir}/backup-*") as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }
}
