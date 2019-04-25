<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;
use Swaggest\JsonDiff\ModifiedPathDiff;

class RearrangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function testKeepOrder()
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
        {"a":1, "b":true}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

        $newJson = <<<'JSON'
{
    "key5": "wat",
    "key1": [5, 1, 2, 3],
    "key4": [
        {"c":false, "a":2}, {"a":1, "b":true}, {"c":1, "a":3}
    ],
    "key3": {
        "sub3": 0,
        "sub2": false,
        "sub1": "c"
    }
}
JSON;

        $expected = <<<'JSON'
{
    "key1": [5, 1, 2, 3],
    "key3": {
        "sub1": "c",
        "sub2": false,
        "sub3": 0
    },
    "key4": [
        {"a":1, "b":true}, {"a":2, "c":false}, {"a":3, "c":1}
    ],
    "key5": "wat"
}
JSON;

        $r = new JsonDiff(json_decode($originalJson), json_decode($newJson), JsonDiff::REARRANGE_ARRAYS + JsonDiff::COLLECT_MODIFIED_DIFF);
        $this->assertSame(
            json_encode(json_decode($expected), JSON_PRETTY_PRINT),
            json_encode($r->getRearranged(), JSON_PRETTY_PRINT)
        );
        $this->assertSame('{"key3":{"sub3":0},"key4":{"1":{"c":false},"2":{"c":1}},"key5":"wat"}',
            json_encode($r->getAdded()));
        $this->assertSame(array(
            '/key3/sub3',
            '/key4/1/c',
            '/key4/2/c',
            '/key5',
        ), $r->getAddedPaths());
        $this->assertSame('{"key2":2,"key3":{"sub0":0},"key4":{"1":{"b":false}}}',
            json_encode($r->getRemoved()));
        $this->assertSame(array(
            '/key2',
            '/key3/sub0',
            '/key4/1/b',
        ), $r->getRemovedPaths());

        $this->assertSame(array(
            '/key1/0',
            '/key3/sub1',
            '/key3/sub2',
        ), $r->getModifiedPaths());

        $this->assertSame('{"key1":[4],"key3":{"sub1":"a","sub2":"b"}}', json_encode($r->getModifiedOriginal()));
        $this->assertSame('{"key1":[5],"key3":{"sub1":"c","sub2":false}}', json_encode($r->getModifiedNew()));

        $this->assertEquals([
            new ModifiedPathDiff('/key1/0', 4, 5),
            new ModifiedPathDiff('/key3/sub1', 'a', 'c'),
            new ModifiedPathDiff('/key3/sub2', 'b', false),
        ], $r->getModifiedDiff());
    }


    public function testRemoved()
    {
        $originalJson = <<<'JSON'
{
    "key2": 2,
    "key3": {
        "sub0": 0,
        "sub1": "a",
        "sub2": "b"
    },
    "key4": [
        {"a":1, "b":true}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

        $newJson = <<<'JSON'
{
    "key3": {
        "sub3": 0,
        "sub2": false,
        "sub1": "c"
    }
}
JSON;

        $expected = <<<'JSON'
{
    "key2": 2,
    "key3": {
        "sub0": 0
    },
    "key4": [
        {"a":1, "b":true}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

        $r = new JsonDiff(json_decode($originalJson), json_decode($newJson));
        $this->assertSame(array(
            '/key2',
            '/key3/sub0',
            '/key4',
        ), $r->getRemovedPaths());

        $this->assertSame(3, $r->getRemovedCnt());

        $this->assertSame(
            json_encode(json_decode($expected), JSON_PRETTY_PRINT),
            json_encode($r->getRemoved(), JSON_PRETTY_PRINT)
        );

    }


    public function testNull()
    {
        $originalJson = <<<'JSON'
{
    "key2": 2,
    "key3": null,
    "key4": [
        {"a":1, "b":true}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

        $newJson = <<<'JSON'
{
    "key3": null
}
JSON;

        $expected = <<<'JSON'
{
    "key2": 2,
    "key4": [
        {"a":1, "b":true}, {"a":2, "b":false}, {"a":3}
    ]
}
JSON;

        $r = new JsonDiff(json_decode($originalJson), json_decode($newJson));
        $this->assertSame(array(
            '/key2',
            '/key4',
        ), $r->getRemovedPaths());

        $this->assertSame(2, $r->getRemovedCnt());

        $this->assertSame(
            json_encode(json_decode($expected), JSON_PRETTY_PRINT),
            json_encode($r->getRemoved(), JSON_PRETTY_PRINT)
        );

    }

    /**
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function testDiff()
    {
        $originalJson = <<<'JSON'
[{"a":1,"b":true,"subs":[{"s":1},{"s":2},{"s":3}]},{"a":2,"b":false},{"a":3}]
JSON;

        $newJson = <<<'JSON'
[{"c":false,"a":2},{"a":1,"b":true,"subs":[{"s":3, "add": true},{"s":2},{"s":1}]},{"c":1,"a":3}]
JSON;

        $rearrangedJson = <<<'JSON'
[{"a":1,"b":true,"subs":[{"s":1},{"s":2},{"s":3,"add":true}]},{"a":2,"c":false},{"a":3,"c":1}]
JSON;


        $patchJson = <<<'JSON'
[
    {"value":true,"op":"add","path":"/0/subs/2/add"},
    {"op":"remove","path":"/1/b"},
    {"value":false,"op":"add","path":"/1/c"},
    {"value":1,"op":"add","path":"/2/c"}
]
JSON;


        $diff = new JsonDiff(json_decode($originalJson), json_decode($newJson), JsonDiff::REARRANGE_ARRAYS);
        $this->assertEquals(json_decode($patchJson), $diff->getPatch()->jsonSerialize());

        $original = json_decode($originalJson);
        $patch = JsonPatch::import(json_decode($patchJson));
        $patch->apply($original);
        $this->assertEquals($rearrangedJson, json_encode($original, JSON_UNESCAPED_SLASHES));
    }


}