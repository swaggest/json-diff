<?php

namespace Swaggest\JsonDiff;


use Swaggest\JsonDiff\JsonPatch\OpPath;
use Throwable;

class PathException extends Exception
{
    /** @var OpPath */
    private $operation;

    /** @var string */
    private $field;

    /**
     * @param string $message
     * @param OpPath $operation
     * @param string $field
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message,
        $operation,
        $field,
        $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
        $this->operation = $operation;
        $this->field = $field;
    }

    /**
     * @return OpPath
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
