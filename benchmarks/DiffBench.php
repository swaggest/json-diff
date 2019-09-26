<?php

use Swaggest\JsonDiff\JsonDiff;

class DiffBench
{
    static $simpleOriginal;
    static $simpleNew;

    static $original;
    static $new;

    public function benchSimpleSkipPatch()
    {
        new JsonDiff(self::$simpleOriginal, self::$simpleNew, JsonDiff::REARRANGE_ARRAYS);
    }

    public function benchSimpleRearrange()
    {
        new JsonDiff(self::$simpleOriginal, self::$simpleNew, JsonDiff::REARRANGE_ARRAYS);
    }

    public function benchSkipPatch()
    {
        new JsonDiff(self::$original, self::$new, JsonDiff::REARRANGE_ARRAYS);
    }

    public function benchRearrange()
    {
        new JsonDiff(self::$original, self::$new, JsonDiff::REARRANGE_ARRAYS);
    }

    public function benchStopOnDiff()
    {
        new JsonDiff(self::$original, self::$new, JsonDiff::STOP_ON_DIFF);
    }


    static function init()
    {
        self::$simpleOriginal = (object)(array("root" => (object)array("a" => 1, "b" => 2)));
        self::$simpleNew = (object)(array("root" => (object)array("b" => 3, "c" => 4)));
        self::$original = json_decode(<<<'JSON'
{
  "key1": [
    4,
    1,
    2,
    3
  ],
  "key2": 2,
  "key3": {
    "sub0": 0,
    "sub1": "a",
    "sub2": "b"
  },
  "key4": [
    {
      "a": 1,
      "b": true
    },
    {
      "a": 2,
      "b": false
    },
    {
      "a": 3
    }
  ]
}
JSON
        );

        self::$new = json_decode(<<<'JSON'
{
  "key5": "wat",
  "key1": [
    5,
    1,
    2,
    3
  ],
  "key4": [
    {
      "c": false,
      "a": 2
    },
    {
      "a": 1,
      "b": true
    },
    {
      "c": 1,
      "a": 3
    }
  ],
  "key3": {
    "sub3": 0,
    "sub2": false,
    "sub1": "c"
  }
}
JSON
        );
    }

}

DiffBench::init();