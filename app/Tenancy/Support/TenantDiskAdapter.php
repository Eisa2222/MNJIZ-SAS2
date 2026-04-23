<?php

declare(strict_types=1);

namespace App\Tenancy\Support;

use App\Tenancy\TenantContext;
use Illuminate\Contracts\Filesystem\Filesystem;
use RuntimeException;

/**
 * Transparent wrapper around an existing disk that automatically prepends
 * tenants/{tenant_id}/ to every path. Keeps controllers & services from
 * having to remember to do it themselves.
 */
final class TenantDiskAdapter implements Filesystem
{
    public function __construct(private Filesystem $inner) {}

    private function prefix(string $path): string
    {
        $tenantId = TenantContext::currentId();

        if ($tenantId === null) {
            throw new RuntimeException('TenantDiskAdapter used without a resolved tenant.');
        }

        $root = config('tenancy.isolation.storage_root', 'tenants');

        return sprintf('%s/%d/%s', $root, $tenantId, ltrim($path, '/\\'));
    }

    public function exists($path)           { return $this->inner->exists($this->prefix($path)); }
    public function get($path)              { return $this->inner->get($this->prefix($path)); }
    public function readStream($path)       { return $this->inner->readStream($this->prefix($path)); }
    public function put($path, $contents, $options = [])           { return $this->inner->put($this->prefix($path), $contents, $options); }
    public function writeStream($path, $resource, array $options = []) { return $this->inner->writeStream($this->prefix($path), $resource, $options); }
    public function getVisibility($path)    { return $this->inner->getVisibility($this->prefix($path)); }
    public function setVisibility($path, $visibility) { return $this->inner->setVisibility($this->prefix($path), $visibility); }
    public function prepend($path, $data)   { return $this->inner->prepend($this->prefix($path), $data); }
    public function append($path, $data)    { return $this->inner->append($this->prefix($path), $data); }
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : [$paths];

        return $this->inner->delete(array_map(fn ($p) => $this->prefix($p), $paths));
    }
    public function copy($from, $to)        { return $this->inner->copy($this->prefix($from), $this->prefix($to)); }
    public function move($from, $to)        { return $this->inner->move($this->prefix($from), $this->prefix($to)); }
    public function size($path)             { return $this->inner->size($this->prefix($path)); }
    public function lastModified($path)     { return $this->inner->lastModified($this->prefix($path)); }
    public function files($directory = null, $recursive = false)         { return $this->inner->files($this->prefix((string) $directory), $recursive); }
    public function allFiles($directory = null)                          { return $this->inner->allFiles($this->prefix((string) $directory)); }
    public function directories($directory = null, $recursive = false)   { return $this->inner->directories($this->prefix((string) $directory), $recursive); }
    public function allDirectories($directory = null)                    { return $this->inner->allDirectories($this->prefix((string) $directory)); }
    public function makeDirectory($path)     { return $this->inner->makeDirectory($this->prefix($path)); }
    public function deleteDirectory($directory) { return $this->inner->deleteDirectory($this->prefix($directory)); }

    public function __call(string $method, array $arguments)
    {
        return $this->inner->{$method}(...$arguments);
    }
}
