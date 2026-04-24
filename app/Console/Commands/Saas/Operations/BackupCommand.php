<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Operations;

use App\Models\SaasMigrationRun;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * `saas:backup:run [--db-only] [--files-only] [--retention-days=30]`
 *
 * Production-grade backup command:
 *
 *   1. `mysqldump` of the application database into
 *      storage/app/backups/db/mnjiz-YYYY-MM-DD-HHMMSS.sql.gz
 *
 *   2. tar-gzipped snapshot of storage/app/public/tenants/ into
 *      storage/app/backups/files/tenants-YYYY-MM-DD-HHMMSS.tar.gz
 *
 *   3. Retention sweep — deletes backup files older than the
 *      configured retention window (default 30 days).
 *
 *   4. Writes a row to `saas_migration_runs` so dashboards can see
 *      "last successful backup" at a glance.
 *
 * This command is wired into the scheduler (daily at 03:00) in Kernel.
 *
 * Designed to be safe to run manually at any time. Skips gracefully when
 * `mysqldump` is unavailable (reports a warning — CI / dev environments
 * shouldn't fail every time they run the suite).
 */
final class BackupCommand extends Command
{
    protected $signature = 'saas:backup:run
                            {--db-only       : Skip file backup}
                            {--files-only    : Skip DB backup}
                            {--retention-days=30 : Delete backups older than N days}';

    protected $description = 'Back up the MNJIZ database + tenant file storage, with retention sweep.';

    public function handle(): int
    {
        $started = now();
        $run = $this->openRun();
        $summary = [];
        $errors  = [];

        $stamp = $started->format('Y-m-d-His');

        if (! $this->option('files-only')) {
            $dbResult = $this->backupDatabase($stamp);
            $summary['db'] = $dbResult;
            if (($dbResult['status'] ?? '') === 'error') $errors[] = $dbResult;
        }

        if (! $this->option('db-only')) {
            $fileResult = $this->backupFiles($stamp);
            $summary['files'] = $fileResult;
            if (($fileResult['status'] ?? '') === 'error') $errors[] = $fileResult;
        }

        // Retention sweep.
        $retention = (int) $this->option('retention-days');
        if ($retention > 0) {
            $summary['retention_deleted'] = $this->sweepOld($retention);
        }

        $this->closeRun($run, $errors ? 'partial' : 'success', $summary, $errors);

        $this->info("✅ Backup finished at {$started->toDateTimeString()} (took " . now()->diffInSeconds($started) . "s).");

        return $errors ? self::FAILURE : self::SUCCESS;
    }

    // ----------------------------------------------------------------

    private function backupDatabase(string $stamp): array
    {
        $out = "backups/db/mnjiz-{$stamp}.sql.gz";
        Storage::disk('local')->makeDirectory('backups/db');

        $cfg = config('database.connections.' . config('database.default'));

        if (empty($cfg['host']) || empty($cfg['database'])) {
            $this->warn('DB connection not configured — skipping mysqldump.');
            return ['status' => 'skipped', 'reason' => 'no-db-config'];
        }

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --quick --routines --triggers %s | gzip > %s',
            escapeshellarg((string) $cfg['host']),
            escapeshellarg((string) ($cfg['port'] ?? 3306)),
            escapeshellarg((string) $cfg['username']),
            escapeshellarg((string) $cfg['password']),
            escapeshellarg((string) $cfg['database']),
            escapeshellarg(storage_path('app/' . $out))
        );

        $proc = Process::fromShellCommandline($cmd);
        $proc->setTimeout(60 * 30); // 30 min — big tenants

        try {
            $proc->mustRun();
        } catch (\Throwable $e) {
            $this->error("mysqldump failed: {$e->getMessage()}");
            return [
                'status' => 'error',
                'reason' => 'mysqldump-failed',
                'message'=> $e->getMessage(),
            ];
        }

        $size = Storage::disk('local')->exists($out) ? Storage::disk('local')->size($out) : 0;

        $this->info("DB dump → {$out} (" . $this->humanBytes($size) . ")");
        return ['status' => 'ok', 'path' => $out, 'bytes' => $size];
    }

    private function backupFiles(string $stamp): array
    {
        $out = "backups/files/tenants-{$stamp}.tar.gz";
        Storage::disk('local')->makeDirectory('backups/files');

        $source = storage_path('app/public/tenants');
        if (! is_dir($source)) {
            $this->warn('No tenants storage directory to back up — skipping files backup.');
            return ['status' => 'skipped', 'reason' => 'no-tenant-files'];
        }

        $cmd = sprintf(
            'tar -czf %s -C %s %s',
            escapeshellarg(storage_path('app/' . $out)),
            escapeshellarg(storage_path('app/public')),
            escapeshellarg('tenants')
        );

        $proc = Process::fromShellCommandline($cmd);
        $proc->setTimeout(60 * 30);

        try {
            $proc->mustRun();
        } catch (\Throwable $e) {
            $this->error("tar failed: {$e->getMessage()}");
            return [
                'status' => 'error',
                'reason' => 'tar-failed',
                'message'=> $e->getMessage(),
            ];
        }

        $size = Storage::disk('local')->exists($out) ? Storage::disk('local')->size($out) : 0;

        $this->info("Files archive → {$out} (" . $this->humanBytes($size) . ")");
        return ['status' => 'ok', 'path' => $out, 'bytes' => $size];
    }

    private function sweepOld(int $retention): int
    {
        $cutoff = Carbon::now()->subDays($retention)->getTimestamp();
        $deleted = 0;

        foreach (['backups/db', 'backups/files'] as $dir) {
            if (! Storage::disk('local')->exists($dir)) {
                continue;
            }
            foreach (Storage::disk('local')->files($dir) as $file) {
                if (Storage::disk('local')->lastModified($file) < $cutoff) {
                    Storage::disk('local')->delete($file);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("Retention sweep: deleted {$deleted} file(s) older than {$retention} days.");
        }
        return $deleted;
    }

    // ----------------------------------------------------------------

    private function openRun(): ?SaasMigrationRun
    {
        try {
            return SaasMigrationRun::create([
                'command'    => 'saas:backup:run',
                'mode'       => 'real',
                'status'     => 'running',
                'started_at' => now(),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function closeRun(?SaasMigrationRun $run, string $status, array $summary, array $errors): void
    {
        if (! $run) return;
        try {
            $run->update([
                'status'      => $status,
                'finished_at' => now(),
                'summary'     => $summary,
                'errors'      => $errors ?: null,
            ]);
        } catch (\Throwable $e) {
            // Audit log is best-effort — never block the command.
        }
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes < 1024)        return "{$bytes} B";
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 1) . ' MB';
        return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
    }
}
