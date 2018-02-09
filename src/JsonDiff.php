<?php

namespace Swaggest\JsonDiff;

use Swaggest\JsonDiff\JsonPatch\Add;
use Swaggest\JsonDiff\JsonPatch\Remove;
use Swaggest\JsonDiff\JsonPatch\Replace;
use Swaggest\JsonDiff\JsonPatch\Test;

class JsonDiff
{
    const REARRANGE_ARRAYS = 1;
    const STOP_ON_DIFF = 2;

    /**
     * Use URI Fragment Identifier Representation will be used (example: "#/c%25d").
     * If not set default JSON String Representation (example: "/c%d").
     */
    const JSON_URI_FRAGMENT_ID = 4;

    private $options = 0;
    private $original;
    private $new;

    private $added;
    private $addedCnt = 0;
    private $addedPaths = array();

    private $removed;
    private $removedCnt = 0;
    private $removedPaths = array();

    private $modifiedOriginal;
    private $modifiedNew;
    private $modifiedCnt = 0;
    private $modifiedPaths = array();

    private $path = '';
    private $pathItems = array();

    private $rearranged;

    /** @var JsonPatch */
    private $jsonPatch;

    /**
     * Processor constructor.
     * @param mixed $original
     * @param mixed $new
     * @param int $options
     * @throws Exception
     */
    public function __construct($original, $new, $options = 0)
    {
        $this->jsonPatch = new JsonPatch();

        $this->original = $original;
        $this->new = $new;
        $this->options = $options;

        if ($options & self::JSON_URI_FRAGMENT_ID) {
            $this->path = '#';
        }

        $this->rearranged = $this->rearrange();
    }

    /**
     * Returns total number of differences
     * @return int
     */
    public function getDiffCnt()
    {
        return $this->addedCnt + $this->modifiedCnt + $this->removedCnt;
    }

    /**
     * Returns removals as partial value of original.
     * @return mixed
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    /**
     * Returns list of `JSON` paths that were removed from original.
     * @return array
     */
    public function getRemovedPaths()
    {
        return $this->removedPaths;
    }

    /**
     * Returns number of removals.
     * @return int
     */
    public function getRemovedCnt()
    {
        return $this->removedCnt;
    }

    /**
     * Returns additions as partial value of new.
     * @return mixed
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * Returns number of additions.
     * @return int
     */
    public function getAddedCnt()
    {
        return $this->addedCnt;
    }

    /**
     * Returns list of `JSON` paths that were added to new.
     * @return array
     */
    public function getAddedPaths()
    {
        return $this->addedPaths;
    }

    /**
     * Returns changes as partial value of original.
     * @return mixed
     */
    public function getModifiedOriginal()
    {
        return $this->modifiedOriginal;
    }

    /**
     * Returns changes as partial value of new.
     * @return mixed
     */
    public function getModifiedNew()
    {
        return $this->modifiedNew;
    }

    /**
     * Returns number of changes.
     * @return int
     */
    public function getModifiedCnt()
    {
        return $this->modifiedCnt;
    }

    /**
     * Returns list of `JSON` paths that were changed from original to new.
     * @return array
     */
    public function getModifiedPaths()
    {
        return $this->modifiedPaths;
    }

    /**
     * Returns new value, rearranged with original order.
     * @return array|object
     */
    public function getRearranged()
    {
        return $this->rearranged;
    }

    /**
     * Returns JsonPatch of difference
     * @return JsonPatch
     */
    public function getPatch()
    {
        return $this->jsonPatch;
    }

    /**
     * @return array|null|object|\stdClass
     * @throws Exception
     */
    private function rearrange()
    {
        return $this->process($this->original, $this->new);
    }

    /**
     * @param mixed $original
     * @param mixed $new
     * @return array|null|object|\stdClass
     * @throws Exception
     */
    private function process($original, $new)
    {
        if (
            (!$original instanceof \stdClass && !is_array($original))
            || (!$new instanceof \stdClass && !is_array($new))
        ) {
            if ($original !== $new) {
                $this->modifiedCnt++;
                if ($this->options & self::STOP_ON_DIFF) {
                    return null;
                }
                $this->modifiedPaths [] = $this->path;

                $this->jsonPatch->op(new Test($this->path, $original));
                $this->jsonPatch->op(new Replace($this->path, $new));

                JsonPointer::add($this->modifiedOriginal, $this->pathItems, $original);
                JsonPointer::add($this->modifiedNew, $this->pathItems, $new);

            }
            return $new;
        }

        if (
            ($this->options & self::REARRANGE_ARRAYS)
            && is_array($original) && is_array($new)
        ) {
            $new = $this->rearrangeArray($original, $new);
        }

        $newArray = $new instanceof \stdClass ? get_object_vars($new) : $new;
        $newOrdered = array();

        $originalKeys = $original instanceof \stdClass ? get_object_vars($original) : $original;
        $isArray = is_array($original);
        $removedOffset = 0;

        foreach ($originalKeys as $key => $originalValue) {
            if ($this->options & self::STOP_ON_DIFF) {
                if ($this->modifiedCnt || $this->addedCnt || $this->removedCnt) {
                    return null;
                }
            }

            $path = $this->path;
            $pathItems = $this->pathItems;
            $actualKey = $key;
            if ($isArray) {
                $actualKey -= $removedOffset;
            }
            $this->path .= '/' . JsonPointer::escapeSegment($actualKey, $this->options & self::JSON_URI_FRAGMENT_ID);
            $this->pathItems[] = $actualKey;

            if (array_key_exists($key, $newArray)) {
                $newOrdered[$key] = $this->process($originalValue, $newArray[$key]);
                unset($newArray[$key]);
            } else {
                $this->removedCnt++;
                if ($this->options & self::STOP_ON_DIFF) {
                    return null;
                }
                $this->removedPaths [] = $this->path;
                if ($isArray) {
                    $removedOffset++;
                }

                $this->jsonPatch->op(new Remove($this->path));

                JsonPointer::add($this->removed, $this->pathItems, $originalValue);
            }
            $this->path = $path;
            $this->pathItems = $pathItems;
        }

        // additions
        foreach ($newArray as $key => $value) {
            $this->addedCnt++;
            if ($this->options & self::STOP_ON_DIFF) {
                return null;
            }
            $newOrdered[$key] = $value;
            $path = $this->path . '/' . JsonPointer::escapeSegment($key, $this->options & self::JSON_URI_FRAGMENT_ID);
            $pathItems = $this->pathItems;
            $pathItems[] = $key;
            JsonPointer::add($this->added, $pathItems, $value);
            $this->addedPaths [] = $path;

            $this->jsonPatch->op(new Add($path, $value));

        }

        return is_array($new) ? $newOrdered : (object)$newOrdered;
    }

    private function rearrangeArray(array $original, array $new)
    {
        $first = reset($original);
        if (!$first instanceof \stdClass) {
            return $new;
        }

        $uniqueKey = false;
        $uniqueIdx = array();

        // find unique key for all items
        $f = get_object_vars($first);
        foreach ($f as $key => $value) {
            if (is_array($value) || $value instanceof \stdClass) {
                continue;
            }

            $keyIsUnique = true;
            $uniqueIdx = array();
            foreach ($original as $item) {
                if (!$item instanceof \stdClass) {
                    return $new;
                }
                if (!isset($item->$key)) {
                    $keyIsUnique = false;
                    break;
                }
                $value = $item->$key;
                if ($value instanceof \stdClass || is_array($value)) {
                    $keyIsUnique = false;
                    break;
                }

                if (isset($uniqueIdx[$value])) {
                    $keyIsUnique = false;
                    break;
                }
                $uniqueIdx[$value] = true;
            }

            if ($keyIsUnique) {
                $uniqueKey = $key;
                break;
            }
        }

        if ($uniqueKey) {
            $newIdx = array();
            foreach ($new as $item) {
                if (!$item instanceof \stdClass) {
                    return $new;
                }

                if (!property_exists($item, $uniqueKey)) {
                    return $new;
                }

                $value = $item->$uniqueKey;

                if ($value instanceof \stdClass || is_array($value)) {
                    return $new;
                }

                if (isset($newIdx[$value])) {
                    return $new;
                }

                $newIdx[$value] = $item;
            }

            $newRearranged = array();
            foreach ($uniqueIdx as $key => $item) {
                if (isset($newIdx[$key])) {
                    $newRearranged [] = $newIdx[$key];
                    unset($newIdx[$key]);
                }
            }
            foreach ($newIdx as $item) {
                $newRearranged [] = $item;
            }
            return $newRearranged;
        }

        return $new;
    }
}