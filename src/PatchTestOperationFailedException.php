<?php

namespace Swaggest\JsonDiff;


use Throwable;

class PatchTestOperationFailedException extends Exception
{
    /** @var object */
    private $operation;
    /** @var string */
    private $actualValue;

    /**
     * @param object $operation
     * @param string $actualValue
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $operation,
        $actualValue,
        $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct('Test operation ' . json_encode($operation, JSON_UNESCAPED_SLASHES)
            . ' failed: ' . json_encode($actualValue), $code, $previous);
        $this->operation = $operation;
        $this->actualValue = $actualValue;
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
    public function getActualValue()
    {
        return $this->actualValue;
    }
}
