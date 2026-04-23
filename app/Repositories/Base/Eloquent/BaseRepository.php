<?php

namespace App\Repositories\Base\Eloquent;

use App\Repositories\Base\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(): Collection       { return $this->model->all(); }
    public function find(int $id)           { return $this->model->find($id); }
    public function create(array $d)        { return $this->model->create($d); }
    public function update(int $id,array $d): bool
    {
        $m = $this->find($id);
        return $m ? $m->update($d) : false;
    }
    public function delete(int $id): bool
    {
        $m = $this->find($id);
        return $m ? $m->delete() : false;
    }
}
