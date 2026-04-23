<?php

namespace App\Services\Qoyod\Resources\Inventories;

use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Inventories extends AbstractResource implements InventoryResourceInterface
{
    protected static function endpoint(): string
    {
        return '/inventories';
    }

    protected static function wrapper(): string
    {
        return 'inventory';
    }

    public function create(array $data): array
    {
        return $this->client->http()->post($this->client->url(static::endpoint()), $data)->json();
    }

    public function update(int|string $id, array $data): array
    {
        return $this->client->http()
            ->put(
                $this->client->url(static::endpoint() . "/{$id}"),
                $data
            )->json();
    }
}
