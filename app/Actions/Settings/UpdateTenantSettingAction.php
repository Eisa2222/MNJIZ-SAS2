<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\TenantSetting;
use App\Services\Settings\SettingsRepository;

final class UpdateTenantSettingAction
{
    public function __construct(private SettingsRepository $repo) {}

    /**
     * @param  array{group?:string,is_encrypted?:bool,cast?:string,description?:string}  $meta
     */
    public function __invoke(string $key, mixed $value, array $meta = [], ?int $tenantId = null): TenantSetting
    {
        return $this->repo->set($key, $value, $meta, $tenantId);
    }
}
