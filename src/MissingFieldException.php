<?php

namespace Swaggest\JsonDiff;

use Throwable;

class MissingFieldException extends Exception
{
    /** @var string */
    private $missingField;
    /** @var object */
    private $operation;

    /**
     * @param string $missingField
     * @param object $operation
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $missingField,
        $operation,
        $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct('Missing "' . $missingField . '" in operation data', $code, $previous);
        $this->missingField = $missingField;
        $this->operation = $operation;
    }

    /**
     * @return string
     */
    public function getMissingField()
    {
        return $this->missingField;
    }

    /**
     * @return object
     */
    public function getOperation()
    {
        return $this->operation;
    }
}
