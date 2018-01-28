<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;

class JsonPatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws Exception
     */
    public function testImportExport()
    {
        $data = json_decode(<<<'JSON'
[
  { "op": "replace", "path": "/baz", "value": "boo" },
  { "op": "add", "path": "/hello", "value": ["world"] },
  { "op": "remove", "path": "/foo"}
]
JSON
        );
        $patch = JsonPatch::import($data);

        $exported = JsonPatch::export($patch);

        $diff = new JsonDiff($data, $exported);
        $this->assertSame(0, $diff->getDiffCnt());
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

        $r = new JsonDiff(json_decode($originalJson), json_decode($newJson), JsonDiff::JSON_URI_FRAGMENT_ID);
        $this->assertSame(array(
            '#/key2',
            '#/key4',
        ), $r->getRemovedPaths());

        $this->assertSame(2, $r->getRemovedCnt());

        $this->assertSame(
            json_encode(json_decode($expected), JSON_PRETTY_PRINT),
            json_encode($r->getRemoved(), JSON_PRETTY_PRINT)
        );

    }

}