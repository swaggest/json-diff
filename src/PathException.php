<?php

namespace Swaggest\JsonDiff;


use Throwable;

class PathException extends Exception
{
    /** @var object */
    private $operation;

    /** @var string */
    private $field;

    /**
     * @param string $message
     * @param object $operation
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
     * @return object
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
