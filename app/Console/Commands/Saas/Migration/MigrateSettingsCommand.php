<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Migration;

use App\Models\Tenant;
use App\Services\Settings\SettingsRepository;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `saas:migration:migrate-settings [--dry-run]`
 *
 * Reads every column of the legacy `settings` row owned by each tenant
 * (one row per tenant after the Module 5 migration) and re-writes those
 * values into the generic `tenant_settings` key/value store via
 * SettingsRepository::set() — which transparently encrypts
 * `is_encrypted=true` values at rest.
 *
 * The legacy `settings` table is NOT dropped; production keeps it until
 * every caller is verified to use SettingsRepository.
 *
 * Dry-run: reports what would be written, per tenant, without touching
 * `tenant_settings`.
 */
final class MigrateSettingsCommand extends BaseSaasMigrationCommand
{
    protected $signature = 'saas:migration:migrate-settings
                            {--dry-run : Compute the plan without writing}';

    protected $description = 'Copy legacy `settings` columns into tenant_settings (encrypted-at-rest for sensitive keys).';

    public function handle(): int
    {
        $this->startRun();

        if (! Schema::hasTable('settings')) {
            $this->warn('Legacy `settings` table does not exist — nothing to migrate.');
            $this->finishRun('success');
            return self::SUCCESS;
        }

        $map = config('saas_migration.settings_map', []);
        if (empty($map)) {
            $this->warn('config/saas_migration.php :: settings_map is empty — nothing to migrate.');
            $this->finishRun('success');
            return self::SUCCESS;
        }

        /** @var SettingsRepository $repo */
        $repo = app(SettingsRepository::class);

        $legacyRows = DB::table('settings')->get();
        if ($legacyRows->isEmpty()) {
            $this->info('No rows in legacy `settings`. Nothing to copy.');
            $this->finishRun('success');
            return self::SUCCESS;
        }

        $perTenant = [];
        $totalWrites = 0;

        foreach ($legacyRows as $row) {
            $tenantId = $row->tenant_id ?? null;
            if (! $tenantId) {
                $this->noteError('migrate-settings', "Legacy settings row without tenant_id (id={$row->id}) — skipped");
                continue;
            }

            $tenant = Tenant::query()->find($tenantId);
            if (! $tenant) {
                $this->noteError('migrate-settings', "tenant_id={$tenantId} from settings row not found in tenants table", ['settings_row_id' => $row->id]);
                continue;
            }

            $wroteKeys = 0;
            $skippedKeys = 0;

            foreach ($map as $column => $meta) {
                if (! property_exists($row, $column)) {
                    $skippedKeys++;
                    continue;
                }

                $value = $row->{$column};
                if ($value === null || $value === '') {
                    $skippedKeys++;
                    continue;
                }

                // Legacy encrypted columns (Crypt via accessor) need decryption
                // BEFORE we re-encrypt through SettingsRepository. The legacy
                // accessor runs when we access via Eloquent — but here we hit
                // raw DB. Attempt Crypt; on failure treat as plaintext.
                if ($meta['is_encrypted'] ?? false) {
                    try {
                        $value = \Illuminate\Support\Facades\Crypt::decryptString((string) $value);
                    } catch (\Throwable $e) {
                        // not encrypted yet — use as-is
                    }
                }

                // JSON columns were stored as text; decode so the setter re-serializes cleanly.
                if (($meta['cast'] ?? 'string') === 'json' && is_string($value)) {
                    $decoded = json_decode($value, true);
                    if ($decoded !== null) $value = $decoded;
                }

                if ($this->isDryRun()) {
                    $wroteKeys++;
                    continue;
                }

                // Use TenantContext::runAs so the creating hook gets a tenant_id.
                // Repository bypasses scope internally but the model still needs
                // the context for BelongsToTenant mutation guard.
                TenantContext::runAs($tenant, function () use ($repo, $column, $value, $meta, $tenantId) {
                    $repo->set($column, $value, [
                        'group'        => $meta['group']        ?? 'general',
                        'is_encrypted' => $meta['is_encrypted'] ?? false,
                        'cast'         => $meta['cast']         ?? 'string',
                        'description'  => 'migrated from legacy settings on ' . now()->toDateString(),
                    ], $tenantId);
                });

                $wroteKeys++;
                $totalWrites++;
            }

            $perTenant[] = [
                'tenant_id'    => $tenantId,
                'tenant_slug'  => $tenant->slug,
                'wrote_keys'   => $wroteKeys,
                'skipped_keys' => $skippedKeys,
                'mode'         => $this->mode(),
            ];
        }

        $this->info(sprintf('— MIGRATE LEGACY SETTINGS (mode=%s) —', $this->mode()));
        $this->table(['tenant_id', 'tenant_slug', 'wrote_keys', 'skipped_keys', 'mode'], $perTenant);
        $this->line(sprintf('  Total settings writes: %d', $totalWrites));

        $this->metric('tenants_processed', count($perTenant));
        $this->metric('total_writes',      $totalWrites);
        $this->metric('per_tenant',        $perTenant);

        $this->finishRun($this->errors ? 'partial' : 'success');

        return self::SUCCESS;
    }
}
