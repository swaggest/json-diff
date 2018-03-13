<?php

namespace Swaggest\JsonDiff;


class JsonPointer
{
    /**
     * @param string $key
     * @param bool $isURIFragmentId
     * @return string
     */
    public static function escapeSegment($key, $isURIFragmentId = false)
    {
        if ($isURIFragmentId) {
            return str_replace(array('%2F', '%7E'), array('~0', '~1'), urlencode($key));
        } else {
            return str_replace(array('~', '/'), array('~0', '~1'), $key);
        }
    }

    /**
     * @param string $path
     * @return string[]
     * @throws Exception
     */
    public static function splitPath($path)
    {
        $pathItems = explode('/', $path);
        $first = array_shift($pathItems);
        if ($first === '#') {
            return self::splitPathURIFragment($pathItems);
        } else {
            if ($first !== '') {
                throw new Exception('Path must start with "/": ' . $path);
            }
            return self::splitPathJsonString($pathItems);
        }
    }

    private static function splitPathURIFragment(array $pathItems)
    {
        $result = array();
        foreach ($pathItems as $key) {
            $key = str_replace(array('~1', '~0'), array('/', '~'), urldecode($key));
            $result[] = $key;
        }
        return $result;
    }

    private static function splitPathJsonString(array $pathItems)
    {
        $result = array();
        foreach ($pathItems as $key) {
            $key = str_replace(array('~1', '~0'), array('/', '~'), $key);
            $result[] = $key;
        }
        return $result;
    }

    /**
     * @param mixed $holder
     * @param string[] $pathItems
     * @param mixed $value
     * @param bool $recursively
     * @throws Exception
     */
    public static function add(&$holder, $pathItems, $value, $recursively = true)
    {
        $ref = &$holder;
        while (null !== $key = array_shift($pathItems)) {
            if ($ref instanceof \stdClass) {
                if (PHP_VERSION_ID < 71000 && '' === $key) {
                    throw new Exception('Empty property name is not supported by PHP <7.1',
                        Exception::EMPTY_PROPERTY_NAME_UNSUPPORTED);
                }

                $ref = &$ref->$key;
            } else { // null or array
                $intKey = filter_var($key, FILTER_VALIDATE_INT);
                if ($ref === null && (false === $intKey || $intKey !== 0)) {
                    $key = (string)$key;
                    if ($recursively) {
                        $ref = new \stdClass();
                        $ref = &$ref->{$key};
                    } else {
                        throw new Exception('Non-existent path');
                    }
                } else {
                    if ($recursively && $ref === null) $ref = array();
                    if ('-' === $key) {
                        $ref = &$ref[];
                    } else {
                        if (is_array($ref) && array_key_exists($key, $ref) && empty($pathItems)) {
                            array_splice($ref, $key, 0, array($value));
                        }
                        if (false === $intKey) {
                            throw new Exception('Invalid key for array operation');
                        }
                        if ($intKey > count($ref) && !$recursively) {
                            throw new Exception('Index is greater than number of items in array');
                        } elseif ($intKey < 0) {
                            throw new Exception('Negative index');
                        }

                        $ref = &$ref[$intKey];
                    }
                }
            }
        }
        $ref = $value;
    }

    private static function arrayKeyExists($key, array $a)
    {
        if (array_key_exists($key, $a)) {
            return true;
        }
        $key = (string)$key;
        foreach ($a as $k => $v) {
            if ((string)$k === $key) {
                return true;
            }
        }
        return false;
    }

    private static function arrayGet($key, array $a)
    {
        $key = (string)$key;
        foreach ($a as $k => $v) {
            if ((string)$k === $key) {
                return $v;
            }
        }
        return false;
    }


    /**
     * @param mixed $holder
     * @param string[] $pathItems
     * @return bool|mixed
     * @throws Exception
     */
    public static function get($holder, $pathItems)
    {
        $ref = $holder;
        while (null !== $key = array_shift($pathItems)) {
            if ($ref instanceof \stdClass) {
                if (PHP_VERSION_ID < 71000 && '' === $key) {
                    throw new Exception('Empty property name is not supported by PHP <7.1',
                        Exception::EMPTY_PROPERTY_NAME_UNSUPPORTED);
                }

                $vars = (array)$ref;
                if (self::arrayKeyExists($key, $vars)) {
                    $ref = self::arrayGet($key, $vars);
                } else {
                    throw new Exception('Key not found: ' . $key);
                }
            } elseif (is_array($ref)) {
                if (self::arrayKeyExists($key, $ref)) {
                    $ref = $ref[$key];
                } else {
                    throw new Exception('Key not found: ' . $key);
                }
            } else {
                throw new Exception('Key not found: ' . $key);
            }
        }
        return $ref;
    }

    /**
     * @param mixed $holder
     * @param string $pointer
     * @return bool|mixed
     * @throws Exception
     */
    public static function getByPointer($holder, $pointer)
    {
        return self::get($holder, self::splitPath($pointer));
    }

    /**
     * @param mixed $holder
     * @param string[] $pathItems
     * @return mixed
     * @throws Exception
     */
    public static function remove(&$holder, $pathItems)
    {
        $ref = &$holder;
        while (null !== $key = array_shift($pathItems)) {
            $parent = &$ref;
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
                if ($refKey !== count($parent)) {
                    $parent = array_values($parent);
                }
            }
        }
        return $ref;
    }
}