<?php

namespace Swaggest\JsonDiff;


class JsonValueReplace
{
    private $search;
    private $replace;
    private $path = '';
    private $pathItems = array();

    /**
     * JsonReplace constructor.
     * @param mixed $search
     * @param mixed $replace
     */
    public function __construct($search, $replace)
    {
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * Recursively replaces all nodes equal to `search` value with `replace` value.
     * @param $data
     * @return mixed
     */
    public function process($data)
    {
        if (!is_array($data) && !is_object($data)) {
            return $data === $this->search ? $this->replace : $data;
        }

        $originalKeys = $data instanceof \stdClass ? get_object_vars($data) : $data;

        $diff = new JsonDiff($data, $this->search, JsonDiff::STOP_ON_DIFF);
        if ($diff->getDiffCnt() === 0) {
            return $this->replace;
        }

        $result = array();

        foreach ($originalKeys as $key => $originalValue) {
            $path = $this->path;
            $pathItems = $this->pathItems;
            $actualKey = $key;
            $this->path .= '/' . JsonPointer::escapeSegment($actualKey);
            $this->pathItems[] = $actualKey;

            $result[$key] = $this->process($originalValue);

            $this->path = $path;
            $this->pathItems = $pathItems;
        }

        return $data instanceof \stdClass ? (object)$result : $result;
    }
}