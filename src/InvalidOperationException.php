<?php

namespace Swaggest\JsonDiff;


use Throwable;

class InvalidOperationException extends Exception
{
    private string $invalidOperation;

    public function __construct(
        string    $invalidOperation,
        string    $message = '',
        int       $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->invalidOperation = $invalidOperation;
    }

    public function getInvalidOperation(): string
    {
        return $this->invalidOperation;
    }
}