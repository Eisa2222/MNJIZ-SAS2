<?php

declare(strict_types=1);

namespace App\Tenancy\Support;

use App\Tenancy\TenantContext;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * All tenant file operations MUST go through here. It guarantees every path
 * is prefixed with tenants/{tenant_id}/... so a bug in one tenant's code
 * can never read or write another tenant's files.
 *
 * Usage:
 *     TenantStorage::disk()->put('lawsuits/42/contract.pdf', $pdf);
 *     TenantStorage::path('lawsuits/42/contract.pdf')
 *         // => "tenants/7/lawsuits/42/contract.pdf"
 */
final class TenantStorage
{
    public static function path(string $relative, ?int $tenantId = null): string
    {
        $tenantId ??= TenantContext::currentId();

        if ($tenantId === null) {
            throw new RuntimeException('TenantStorage::path() called with no tenant resolved.');
        }

        $root = config('tenancy.isolation.storage_root', 'tenants');
        $relative = ltrim($relative, '/\\');

        return sprintf('%s/%d/%s', $root, $tenantId, $relative);
    }

    public static function disk(?string $disk = null): Filesystem
    {
        return new TenantDiskAdapter(Storage::disk($disk));
    }
}
