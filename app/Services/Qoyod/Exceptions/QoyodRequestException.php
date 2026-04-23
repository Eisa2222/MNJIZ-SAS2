<?php
namespace App\Services\Qoyod\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class QoyodRequestException extends Exception
{
    public function __construct(
        string $message,
        private readonly ?Response $response = null,
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }

    public function response(): ?Response
    {
        return $this->response;
    }
}
