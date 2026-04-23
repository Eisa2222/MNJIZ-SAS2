<?php

declare(strict_types=1);

namespace App\Exceptions\Billing;

use App\DTOs\Billing\LimitCheckResult;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class UsageLimitExceededException extends RuntimeException implements HttpExceptionInterface
{
    public function __construct(
        public readonly LimitCheckResult $result,
        ?string $message = null,
    ) {
        parent::__construct($message ?? "Usage limit exceeded for feature '{$result->featureKey}'.");
    }

    public function getStatusCode(): int
    {
        return 429; // Too Many Requests — meaningful for rate-like features
    }

    public function getHeaders(): array
    {
        $headers = [
            'X-Feature-Gate' => $this->result->featureKey,
            'X-Feature-Used' => (string) $this->result->used,
        ];

        if ($this->result->limit !== null) {
            $headers['X-Feature-Limit'] = (string) $this->result->limit;
        }

        if ($this->result->resetAt) {
            $headers['X-Feature-Reset'] = $this->result->resetAt->toIso8601String();
        }

        return $headers;
    }
}
