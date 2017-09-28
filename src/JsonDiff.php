<?php

namespace Swaggest\JsonDiff;

class JsonDiff
{
    const SKIP_REARRANGE_ARRAY = 1;

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

    private $path = '#';

    private $rearranged;

    /**
     * Processor constructor.
     * @param $original
     * @param $new
     * @param int $options
     */
    public function __construct($original, $new, $options = 0)
    {
        $this->original = $original;
        $this->new = $new;
        $this->options = $options;

        $this->rearranged = $this->rearrange();
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

    private function rearrange()
    {
        return $this->process($this->original, $this->new);
    }

    private function process($original, $new)
    {
        if (
            (!$original instanceof \stdClass && !is_array($original))
            || (!$new instanceof \stdClass && !is_array($new))
        ) {
            if ($original !== $new) {
                $this->modifiedCnt++;
                $this->modifiedPaths [] = $this->path;
                $this->pushByPath($this->modifiedOriginal, $this->path, $original);
                $this->pushByPath($this->modifiedNew, $this->path, $new);
            }
            return $new;
        }

        if (
            !($this->options & self::SKIP_REARRANGE_ARRAY)
            && is_array($original) && is_array($new)
        ) {
            $new = $this->rearrangeArray($original, $new);
        }

        $newArray = $new instanceof \stdClass ? get_object_vars($new) : $new;
        $newOrdered = array();

        $originalKeys = $original instanceof \stdClass ? get_object_vars($original) : $original;

        foreach ($originalKeys as $key => $originalValue) {
            $path = $this->path;
            $this->path .= '/' . urlencode($key);

            if (isset($newArray[$key])) {
                $newOrdered[$key] = $this->process($originalValue, $newArray[$key]);
                unset($newArray[$key]);
            } else {
                $this->removedCnt++;
                $this->removedPaths [] = $this->path;
                $this->pushByPath($this->removed, $this->path, $originalValue);
            }
            $this->path = $path;
        }

        // additions
        foreach ($newArray as $key => $value) {
            $newOrdered[$key] = $value;
            $path = $this->path . '/' . urlencode($key);
            $this->pushByPath($this->added, $path, $value);
            $this->addedCnt++;
            $this->addedPaths [] = $path;
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

    private function pushByPath(&$holder, $path, $value)
    {
        $pathItems = explode('/', $path);
        if ('#' === $pathItems[0]) {
            array_shift($pathItems);
        }
        $ref = &$holder;
        while (null !== $key = array_shift($pathItems)) {
            $ref = &$ref[(string)$key];
        }
        $ref = $value;
    }
}