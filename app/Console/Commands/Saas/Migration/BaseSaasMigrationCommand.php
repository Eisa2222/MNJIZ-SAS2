<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Migration;

use App\Models\SaasMigrationRun;
use Illuminate\Console\Command;
use Throwable;

/**
 * Shared plumbing for every `saas:migration:*` command.
 *
 * Responsibilities:
 *   - open / close a `saas_migration_runs` audit record
 *   - uniform dry-run option handling
 *   - structured summary + error accumulation
 *   - safe transaction wrapping (in real-mode, can be disabled per-command
 *     for long-running cross-table work)
 */
abstract class BaseSaasMigrationCommand extends Command
{
    protected ?SaasMigrationRun $runRecord = null;
    protected array $summary = [];
    protected array $errors = [];

    protected function isDryRun(): bool
    {
        // Commands register `--dry-run` themselves (if they support it).
        // For commands that don't (e.g. inventory, validate), gracefully
        // fall back to false so the base-class plumbing never errors.
        if (! $this->getDefinition()->hasOption('dry-run')) {
            return false;
        }
        return (bool) $this->option('dry-run');
    }

    protected function mode(): string
    {
        return $this->isDryRun() ? 'dry-run' : 'real';
    }

    /**
     * Start a migration run record. Call at the top of handle().
     */
    protected function startRun(): void
    {
        try {
            // $this->getName() returns the command name only, even when
            // `$signature` contains multi-line arg declarations — safer
            // than parsing the signature ourselves.
            $commandName = method_exists($this, 'getName') ? ($this->getName() ?: static::class) : static::class;

            $this->runRecord = SaasMigrationRun::create([
                'command'    => $commandName,
                'mode'       => $this->mode(),
                'status'     => 'running',
                'started_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Audit log is nice-to-have — never block the command itself.
            $this->warn("Could not open migration-run record: {$e->getMessage()}");
            $this->runRecord = null;
        }
    }

    /**
     * Close the run record with a status. Called from the command's
     * exit path (success or failure).
     */
    protected function finishRun(string $status = 'success'): void
    {
        if (! $this->runRecord) {
            return;
        }

        try {
            $this->runRecord->update([
                'status'      => $status,
                'finished_at' => now(),
                'summary'     => $this->summary,
                'errors'      => $this->errors ?: null,
            ]);
        } catch (Throwable $e) {
            $this->warn("Could not close migration-run record: {$e->getMessage()}");
        }
    }

    /** Record a per-step metric that will be persisted in the summary json. */
    protected function metric(string $key, mixed $value): void
    {
        $this->summary[$key] = $value;
    }

    /** Record a per-row error without aborting the command. */
    protected function noteError(string $context, string $message, array $meta = []): void
    {
        $this->errors[] = array_filter([
            'context' => $context,
            'message' => $message,
            'meta'    => $meta ?: null,
        ]);
    }
}
