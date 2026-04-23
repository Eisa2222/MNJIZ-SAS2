<?php

namespace App\Services\Qoyod\Contracts;

interface CrudResourceInterface
{
    public function all(array $query = []): array;
    public function create(array $payload): array;
    public function find(int|string $id): array;
    public function update(int|string $id, array $payload): array;
    public function delete(int|string $id): bool;
}
