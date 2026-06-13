<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use ZipArchive;

class DatabaseRestore extends Command
{
    protected $signature = 'backup:restore
        {file? : Backup filename (in storage/app/backups) or an absolute path; omit to choose from a list}
        {--force : Skip the confirmation prompt (required in non-interactive use)}
        {--no-safety-backup : Do not take a safety backup of the current database first}
        {--dry-run : Resolve and validate the backup and show the command without changing the database}';

    protected $description = 'Restore the database from a backup created by backup:run';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        $file = $this->argument('file') ?: $this->chooseBackup($backupDir);
        if ($file === null) {
            return Command::FAILURE;
        }

        $path = $this->resolvePath($file, $backupDir);
        if ($path === null) {
            $this->error("Backup not found: {$file}");

            return Command::FAILURE;
        }

        // .zip backups (created with --with-media) hold the SQL dump inside; pull
        // it out to a temp file we can feed to mysql, and clean it up afterwards.
        [$sqlPath, $tempToCleanup] = $this->extractSql($path);
        if ($sqlPath === null) {
            return Command::FAILURE;
        }

        if (! $this->verifyDump($sqlPath)) {
            $this->cleanup($tempToCleanup);

            return Command::FAILURE;
        }

        $config = config('database.connections.mysql');
        $command = $this->buildRestoreCommand($config, $sqlPath);

        if ($this->option('dry-run')) {
            $this->info('Dry run — no changes made. Would restore from: '.basename($path));
            $this->line($this->redact($command, $config['password'] ?? null));
            $this->cleanup($tempToCleanup);

            return Command::SUCCESS;
        }

        $this->warn("This will OVERWRITE the '{$config['database']}' database with the contents of ".basename($path).'.');
        if (! $this->option('force') && ! $this->confirm('Are you sure you want to continue?')) {
            $this->line('Aborted.');
            $this->cleanup($tempToCleanup);

            return Command::FAILURE;
        }

        if (! $this->option('no-safety-backup')) {
            $this->info('Taking a safety backup of the current database first…');
            if (Artisan::call('backup:run') !== Command::SUCCESS) {
                $this->error('Safety backup failed; aborting restore. Re-run with --no-safety-backup to override.');
                $this->cleanup($tempToCleanup);

                return Command::FAILURE;
            }
        }

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        $this->cleanup($tempToCleanup);

        if (! $process->isSuccessful()) {
            $this->error('Database restore failed: '.$process->getErrorOutput());

            return Command::FAILURE;
        }

        $this->info('Database restored from '.basename($path).'.');

        return Command::SUCCESS;
    }

    /**
     * Interactive picker of available backups (newest first).
     */
    private function chooseBackup(string $backupDir): ?string
    {
        $backups = $this->availableBackups($backupDir);

        if (empty($backups)) {
            $this->error("No backups found in {$backupDir}.");

            return null;
        }

        if (! $this->input->isInteractive()) {
            $this->error('No file given. Pass a backup filename, e.g. backup:restore '.$backups[0]);

            return null;
        }

        return $this->choice('Which backup do you want to restore?', $backups, 0);
    }

    /**
     * @return array<int, string> backup filenames, newest first
     */
    private function availableBackups(string $backupDir): array
    {
        $files = glob("{$backupDir}/backup-*.{sql,zip}", GLOB_BRACE) ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map('basename', $files);
    }

    private function resolvePath(string $file, string $backupDir): ?string
    {
        foreach ([$file, "{$backupDir}/".basename($file)] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string} [sqlPath, tempFileToCleanup]
     */
    private function extractSql(string $path): array
    {
        if (! str_ends_with($path, '.zip')) {
            return [$path, null];
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->error('Could not open zip archive: '.basename($path));

            return [null, null];
        }

        // Find the .sql entry (backup:run stores it at the archive root).
        $sqlEntry = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '.sql')) {
                $sqlEntry = $name;
                break;
            }
        }

        if ($sqlEntry === null) {
            $this->error('No .sql file found inside '.basename($path));
            $zip->close();

            return [null, null];
        }

        $temp = tempnam(sys_get_temp_dir(), 'restore_').'.sql';
        copy("zip://{$path}#{$sqlEntry}", $temp);
        $zip->close();

        return [$temp, $temp];
    }

    /**
     * Lightweight integrity check: the dump must be non-empty and look like a
     * real schema dump (contains at least one CREATE TABLE).
     */
    private function verifyDump(string $sqlPath): bool
    {
        if (! is_file($sqlPath) || filesize($sqlPath) === 0) {
            $this->error('Backup file is empty.');

            return false;
        }

        $handle = fopen($sqlPath, 'r');
        $found = false;
        while (($line = fgets($handle)) !== false) {
            if (stripos($line, 'CREATE TABLE') !== false) {
                $found = true;
                break;
            }
        }
        fclose($handle);

        if (! $found) {
            $this->error('Backup does not contain any CREATE TABLE statements; it may be corrupt.');

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function buildRestoreCommand(array $config, string $sqlPath): string
    {
        return sprintf(
            'mysql -h%s -P%s -u%s %s %s < %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            empty($config['password']) ? '' : '-p'.escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($sqlPath)
        );
    }

    private function redact(string $command, ?string $password): string
    {
        if (empty($password)) {
            return $command;
        }

        return str_replace('-p'.escapeshellarg($password), '-p*****', $command);
    }

    private function cleanup(?string $tempFile): void
    {
        if ($tempFile && is_file($tempFile)) {
            unlink($tempFile);
        }
    }
}
