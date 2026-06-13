<?php

namespace Tests\Feature;

use Tests\TestCase;
use ZipArchive;

class DatabaseRestoreTest extends TestCase
{
    private string $backupDir;

    /** @var array<int, string> */
    private array $created = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupDir = storage_path('app/backups');

        if (! is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->created as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    private function writeSqlBackup(string $name, string $contents): string
    {
        $path = "{$this->backupDir}/{$name}";
        file_put_contents($path, $contents);
        $this->created[] = $path;

        return $name;
    }

    private function writeZipBackup(string $name, string $sqlName, string $sql): string
    {
        $path = "{$this->backupDir}/{$name}";
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString($sqlName, $sql);
        $zip->close();
        $this->created[] = $path;

        return $name;
    }

    public function test_dry_run_resolves_and_prints_command_without_changes(): void
    {
        $file = $this->writeSqlBackup('backup-2020-01-01-000000.sql', "CREATE TABLE `items` (id int);\n");

        $this->artisan('backup:restore', ['file' => $file, '--dry-run' => true])
            ->expectsOutputToContain('Dry run')
            ->expectsOutputToContain('mysql -h')
            ->assertExitCode(0);
    }

    public function test_missing_file_fails(): void
    {
        $this->artisan('backup:restore', ['file' => 'does-not-exist.sql', '--dry-run' => true])
            ->expectsOutputToContain('Backup not found')
            ->assertExitCode(1);
    }

    public function test_empty_backup_fails_verification(): void
    {
        $file = $this->writeSqlBackup('backup-2020-01-02-000000.sql', '');

        $this->artisan('backup:restore', ['file' => $file, '--dry-run' => true])
            ->expectsOutputToContain('empty')
            ->assertExitCode(1);
    }

    public function test_backup_without_create_table_fails_verification(): void
    {
        $file = $this->writeSqlBackup('backup-2020-01-03-000000.sql', "-- just a comment\nSELECT 1;\n");

        $this->artisan('backup:restore', ['file' => $file, '--dry-run' => true])
            ->expectsOutputToContain('CREATE TABLE')
            ->assertExitCode(1);
    }

    public function test_zip_backup_is_extracted_and_restored(): void
    {
        $file = $this->writeZipBackup(
            'backup-2020-01-04-000000.zip',
            'backup-2020-01-04-000000.sql',
            "CREATE TABLE `items` (id int);\n"
        );

        $this->artisan('backup:restore', ['file' => $file, '--dry-run' => true])
            ->expectsOutputToContain('Dry run')
            ->assertExitCode(0);
    }

    public function test_confirmation_can_be_declined(): void
    {
        $file = $this->writeSqlBackup('backup-2020-01-05-000000.sql', "CREATE TABLE `items` (id int);\n");

        $this->artisan('backup:restore', ['file' => $file])
            ->expectsConfirmation('Are you sure you want to continue?', 'no')
            ->expectsOutputToContain('Aborted')
            ->assertExitCode(1);
    }

    public function test_password_is_redacted_in_dry_run_output(): void
    {
        config(['database.connections.mysql.password' => 'sup3rsecret']);
        $file = $this->writeSqlBackup('backup-2020-01-06-000000.sql', "CREATE TABLE `items` (id int);\n");

        $this->artisan('backup:restore', ['file' => $file, '--dry-run' => true])
            ->doesntExpectOutputToContain('sup3rsecret')
            ->expectsOutputToContain('-p*****')
            ->assertExitCode(0);
    }
}
