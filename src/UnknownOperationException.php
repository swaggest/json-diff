<?php

namespace Swaggest\JsonDiff;


use Throwable;

class UnknownOperationException extends Exception
{
    /** @var object */
    private $operation;

    /**
     * @param object $operation
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $operation,
        $code = 0,
        Throwable $previous = null
    )
    {
        // @phpstan-ignore-next-line MissingFieldOperation will be thrown if op is not set
        parent::__construct('Unknown "op": ' . $operation->op, $code, $previous);
        $this->operation = $operation;
    }

    /**
     * @return object
     */
    public function getOperation()
    {
        return $this->operation;
    }
}
