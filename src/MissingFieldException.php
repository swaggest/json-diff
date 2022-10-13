<?php

namespace Swaggest\JsonDiff;

use Throwable;

class MissingFieldException extends Exception
{
    private string $missingField;
    private object $operationObject;

    public function __construct(
        string    $missingField,
        object    $operationObject,
        string    $message = '',
        int       $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->missingField = $missingField;
        $this->operationObject = $operationObject;
    }

    public function getMissingField(): string
    {
        return $this->missingField;
    }

    public function getOperationObject(): object
    {
        return $this->operationObject;
    }
}
