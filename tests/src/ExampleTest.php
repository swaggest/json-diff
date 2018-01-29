<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function testDiff()
    {
        $originalJson = <<<'JSON'
{
    "key1": [4, 1, 2, 3],
    "key2": 2,
    "key3": {
        "sub0": 0,
        "sub1": "a",
        "sub2": "b"
    },
    "key4": [
        {"a":1, "b":true, "subs": [{"s":1}, {"s":2}, {"s":3}]}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

        $newJson = <<<'JSON'
{
    "key5": "wat",
    "key1": [5, 1, 2, 3],
    "key4": [
        {"c":false, "a":2}, {"a":1, "b":true, "subs": [{"s":3, "add": true}, {"s":2}, {"s":1}]}, {"c":1, "a":3}
    ],
    "key3": {
        "sub3": 0,
        "sub2": false,
        "sub1": "c"
    }
}
JSON;

        $patchJson = <<<'JSON'
[
    {"value":4,"op":"test","path":"/key1/0"},
    {"value":5,"op":"replace","path":"/key1/0"},
    
    {"op":"remove","path":"/key2"},
    
    {"op":"remove","path":"/key3/sub0"},
    
    {"value":"a","op":"test","path":"/key3/sub1"},
    {"value":"c","op":"replace","path":"/key3/sub1"},
    
    {"value":"b","op":"test","path":"/key3/sub2"},
    {"value":false,"op":"replace","path":"/key3/sub2"},
    
    {"value":0,"op":"add","path":"/key3/sub3"},

    {"value":true,"op":"add","path":"/key4/0/subs/2/add"},
    
    {"op":"remove","path":"/key4/1/b"},
    
    {"value":false,"op":"add","path":"/key4/1/c"},
    
    {"value":1,"op":"add","path":"/key4/2/c"},
    
    {"value":"wat","op":"add","path":"/key5"}
]
JSON;


        $diff = new JsonDiff(json_decode($originalJson), json_decode($newJson), JsonDiff::REARRANGE_ARRAYS);
        $this->assertEquals(json_decode($patchJson), $diff->getPatch()->jsonSerialize());
        $this->assertSame(3, $diff->getModifiedCnt());

        $original = json_decode($originalJson);
        $patch = JsonPatch::import(json_decode($patchJson));
        $patch->apply($original);
        $this->assertEquals($diff->getRearranged(), $original);
    }
}