<?php

namespace Swaggest\JsonDiff;


use Throwable;

class InvalidOperationException extends Exception
{
    private object $operationObject;

    public function __construct(
        object    $operationObject,
        string    $message = '',
        int       $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->operationObject = $operationObject;
    }

    public function getInvalidOperation(): string
    {
        return $this->operationObject->op;
    }

    public function getOperationObject(): object
    {
        return $this->operationObject;
    }
}
