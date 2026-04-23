<?php

namespace App\Services\Qoyod\Resources;

use App\Services\Qoyod\Contracts\CrudResourceInterface;
use App\Services\Qoyod\Contracts\QoyodClientInterface;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use Illuminate\Http\Client\RequestException;


abstract class AbstractResource implements CrudResourceInterface
{
    public function __construct(protected QoyodClientInterface $client) {}

    abstract protected static function endpoint(): string;

    abstract protected static function wrapper(): string;

    public function all(array $query = []): array
    {
        try {
            $response = $this->client->http()
                ->get($this->client->url(static::endpoint()), $query)
                ->throw();

            return $response->json();
        } catch (RequestException $e) {
            $body = $e->response->body();

            throw new QoyodRequestException(
                'خطأ في جلب البيانات: ' . $body,
                $e->response,
                $e->response->status()
            );
        }
    }

    public function find(int|string $id): array
    {
        try {
            $response = $this->client->http()
                ->get($this->client->url(static::endpoint() . "/{$id}"))
                ->throw();

            return $response->json();
        } catch (RequestException $e) {
            $body = $e->response->body();

            throw new QoyodRequestException(
                'خطأ في جلب البيانات: ' . $body,
                $e->response,
                $e->response->status()
            );
        }
    }

    public function create(array $data): array
    {
        try {
            $response = $this->client->http()
                ->post($this->client->url(static::endpoint()), [
                    static::wrapper() => $data,
                ])->throw();

            return $response->json();
        } catch (RequestException $e) {
            $body = $e->response->body();

            throw new QoyodRequestException(
                'خطأ في  اضافة البيانات: ' . $body,
                $e->response,
                $e->response->status()
            );
        }
    }

    public function update(int|string $id, array $data): array
    {
        try {
            $response = $this->client->http()
                ->put($this->client->url(static::endpoint() . "/{$id}"), [
                    static::wrapper() => $data,
                ])->throw();

            return $response->json();
        } catch (RequestException $e) {
            $body = $e->response->body();

            throw new QoyodRequestException(
                'خطأ في تحديث البيانات: ' . $body,
                $e->response,
                $e->response->status()
            );
        }
    }

    public function delete(int|string $id): bool
    {
        try {
            $this->client->http()
                ->delete($this->client->url(static::endpoint() . "/{$id}"))
                ->throw();

            return true;
        } catch (RequestException $e) {
            $body = $e->response->body();

            throw new QoyodRequestException(
                'خطأ في حذف البيانات: ' . $body,
                $e->response,
                $e->response->status()
            );
        }
    }
}
