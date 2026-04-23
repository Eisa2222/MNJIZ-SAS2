<?php

declare(strict_types=1);

namespace App\Exceptions\Billing;

use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Thrown when a controller/action asks a tenant to use a feature that its
 * plan does not include (or the tenant has no plan assigned).
 *
 * HTTP-aware: Laravel renders as 403. Override render() if you need a JSON
 * shape for tenant API endpoints.
 */
class FeatureNotEnabledException extends RuntimeException implements HttpExceptionInterface
{
    public function __construct(
        public readonly string $featureKey,
        public readonly ?int $tenantId = null,
        ?string $message = null,
    ) {
        parent::__construct($message ?? "Feature '{$featureKey}' is not enabled on the current plan.");
    }

    public function getStatusCode(): int
    {
        return 403;
    }

    public function getHeaders(): array
    {
        return ['X-Feature-Gate' => $this->featureKey];
    }
}
