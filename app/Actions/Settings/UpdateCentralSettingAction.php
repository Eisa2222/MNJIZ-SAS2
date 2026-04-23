<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\CentralSetting;
use App\Services\Settings\SettingsRepository;

final class UpdateCentralSettingAction
{
    public function __construct(private SettingsRepository $repo) {}

    /**
     * @param  array{group?:string,is_encrypted?:bool,cast?:string,description?:string}  $meta
     */
    public function __invoke(string $key, mixed $value, array $meta = []): CentralSetting
    {
        return $this->repo->setCentral($key, $value, $meta);
    }
}
