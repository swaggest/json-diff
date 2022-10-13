<?php

namespace Swaggest\JsonDiff;

use Throwable;

class MissingFieldException extends Exception
{
    private string $missingField;

    public function __construct(
        string    $invalidOperation,
        string    $message = '',
        int       $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->missingField = $invalidOperation;
    }

    public function getMissingField(): string
    {
        return $this->missingField;
    }
}
