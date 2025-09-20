<?php

use App\Exceptions\ApiException;

class QuizException extends ApiException
{
    protected $message;
    protected $errors;
    protected $errorCode;

    public function __construct(string $message = "", array $errors = [], ?string $errorCode = null)
    {
        $this->message = $message ?: __("exceptions.api.error.validation");
        $this->errors = $errors;
        $this->errorCode = $errorCode;

        parent::__construct($this->message);
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
