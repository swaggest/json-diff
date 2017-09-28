<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonDiff;

class RearrangeTest extends \PHPUnit_Framework_TestCase
{
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

        $r = new JsonDiff(json_decode($originalJson), json_decode($newJson));
        $this->assertSame(
            json_encode(json_decode($expected), JSON_PRETTY_PRINT),
            json_encode($r->getRearranged(), JSON_PRETTY_PRINT)
        );
        $this->assertSame('{"key3":{"sub3":0},"key4":{"1":{"c":false},"2":{"c":1}},"key5":"wat"}',
            json_encode($r->getAdded()));
        $this->assertSame(array(
            '#/key3/sub3',
            '#/key4/1/c',
            '#/key4/2/c',
            '#/key5',
        ), $r->getAddedPaths());
        $this->assertSame('{"key2":2,"key3":{"sub0":0},"key4":{"1":{"b":false}}}',
            json_encode($r->getRemoved()));
        $this->assertSame(array(
            '#/key2',
            '#/key3/sub0',
            '#/key4/1/b',
        ), $r->getRemovedPaths());

        $this->assertSame(array(
            '#/key1/0',
            '#/key3/sub1',
            '#/key3/sub2',
        ), $r->getModifiedPaths());

        $this->assertSame('{"key1":[4],"key3":{"sub1":"a","sub2":"b"}}', json_encode($r->getModifiedOriginal()));
        $this->assertSame('{"key1":[5],"key3":{"sub1":"c","sub2":false}}', json_encode($r->getModifiedNew()));
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
            '#/key2',
            '#/key3/sub0',
            '#/key4',
        ), $r->getRemovedPaths());

        $this->assertSame(3, $r->getRemovedCnt());

        $this->assertSame(
            json_encode(json_decode($expected), JSON_PRETTY_PRINT),
            json_encode($r->getRemoved(), JSON_PRETTY_PRINT)
        );

    }

}