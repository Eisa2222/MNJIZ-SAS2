<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Services\Settings\SettingsRepository;

final class GetTenantSettingAction
{
    public function __construct(private SettingsRepository $repo) {}

    public function __invoke(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        return $this->repo->get($key, $default, $tenantId);
    }
}
