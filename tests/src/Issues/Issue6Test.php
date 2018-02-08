<?php

namespace Swaggest\JsonDiff\Tests\Issues;

use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;


/**
 * @see https://github.com/swaggest/json-diff/issues/6
 */
class Issue6Test extends \PHPUnit_Framework_TestCase
{
    public function testIssue6()
    {
        $json1 = json_decode('[{"name":"a"},{"name":"b"},{"name":"c"}]');
        $json2 = json_decode('[{"name":"b"}]');

        $diff = new JsonDiff($json1, $json2);
        $patch = $diff->getPatch();

        $this->assertSame(<<<'JSON'
[
    {
        "value": "a",
        "op": "test",
        "path": "/0/name"
    },
    {
        "value": "b",
        "op": "replace",
        "path": "/0/name"
    },
    {
        "op": "remove",
        "path": "/1"
    },
    {
        "op": "remove",
        "path": "/2"
    }
]
JSON
            , json_encode($patch, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));

        $json1a = $json1;
        $patch->apply($json1a);

        $this->assertEquals($json2, $json1a);
    }


    public function testIssue6Remove()
    {
        $json1 = json_decode('[{"name":"a"},{"name":"b"},{"name":"c"}]');
        $json2 = json_decode('[{"name":"b"}]');

        $patch = JsonPatch::import(json_decode('[{"op":"remove","path":"/0"},{"op":"remove","path":"/2"}]'));

        $json1a = $json1;
        $patch->apply($json1a);
        $this->assertEquals(json_encode($json2), json_encode($json1a));
        /*
            Failed asserting that two strings are equal.
            Expected :'[{"name":"b"}]'
            Actual   :'{"1":{"name":"b"}}'
         */
    }
}