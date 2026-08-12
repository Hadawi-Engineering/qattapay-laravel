<?php

namespace QattaPay\Laravel\Exceptions;

use Exception;

class ApiException extends Exception
{
    public readonly string $errorCode;

    public function __construct(
        string $message,
        public readonly int $status = 0,
        string $code = '',
    ) {
        parent::__construct($message);
        $this->errorCode = $code;
    }

    /**
     * API error code from the response body (e.g. `network_error`).
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
