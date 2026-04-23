<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

class PolicyValidationException extends Exception
{
    /**
     * Convert the exception to a ValidationException.
     *
     * @return \Illuminate\Validation\ValidationException
     */
    public function toValidationException()
    {
        return ValidationException::withMessages([
            'policy' => [$this->getMessage()],
        ]);
    }
}
