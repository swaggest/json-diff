<?php

namespace Swaggest\JsonDiff;


class JsonProcessor
{
    public static function pushByPath(&$holder, $path, $value)
    {
        $pathItems = explode('/', $path);
        if ('#' === $pathItems[0]) {
            array_shift($pathItems);
        }
        $ref = &$holder;
        while (null !== $key = array_shift($pathItems)) {
            if (is_string($key)) {
                $key = urldecode($key);
            }
            if ($ref instanceof \stdClass) {
                $ref = &$ref->$key;
            } elseif ($ref === null
                && !is_int($key)
                && false === filter_var($key, FILTER_VALIDATE_INT)
            ) {
                $ref = new \stdClass();
                $ref = &$ref->$key;
            } else {
                $ref = &$ref[$key];
            }
        }
        $ref = $value;
    }

    public static function getByPath(&$holder, $path)
    {
        $pathItems = explode('/', $path);
        if ('#' === $pathItems[0]) {
            array_shift($pathItems);
        }
        $ref = &$holder;
        while (null !== $key = array_shift($pathItems)) {
            $key = urldecode($key);
            if ($ref instanceof \stdClass) {
                if (property_exists($ref, $key)) {
                    $ref = &$ref->$key;
                } else {
                    throw new Exception('Key not found: ' . $key);
                }
            } else {
                if (array_key_exists($key, $ref)) {
                    $ref = &$ref[$key];
                } else {
                    throw new Exception('Key not found: ' . $key);
                }
            }
        }
        return $ref;
    }

    public static function removeByPath(&$holder, $path)
    {
        $pathItems = explode('/', $path);
        if ('#' === $pathItems[0]) {
            array_shift($pathItems);
        }
        $ref = &$holder;
        while (null !== $key = array_shift($pathItems)) {
            $parent = &$ref;
            $key = urldecode($key);
            $refKey = $key;
            if ($ref instanceof \stdClass) {
                if (property_exists($ref, $key)) {
                    $ref = &$ref->$key;
                } else {
                    throw new Exception('Key not found: ' . $key);
                }
            } else {
                if (array_key_exists($key, $ref)) {
                    $ref = &$ref[$key];
                } else {
                    throw new Exception('Key not found: ' . $key);
                }
            }
        }

        if (isset($parent) && isset($refKey)) {
            if ($parent instanceof \stdClass) {
                unset($parent->$refKey);
            } else {
                unset($parent[$refKey]);
            }
        }
        return $ref;
    }
}