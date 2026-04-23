<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Services\Settings\SettingsRepository;

final class GetCentralSettingAction
{
    public function __construct(private SettingsRepository $repo) {}

    public function __invoke(string $key, mixed $default = null): mixed
    {
        return $this->repo->getCentral($key, $default);
    }
}
