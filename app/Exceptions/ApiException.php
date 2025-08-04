<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $errors;
    protected $errorCode;

    public function __construct(string $message = "", array $errors = [], ?string $errorCode = null)
    {
        parent::__construct($message ?: __("exceptions.api.error.validation"));
        $this->errors = $errors;
        $this->errorCode = $errorCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}
