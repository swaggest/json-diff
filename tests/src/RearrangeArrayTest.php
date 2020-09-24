<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonDiff;

class RearrangeArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function testRearrangeArray()
    {
        $oldJson = <<<'JSON'
[
    {
        "name": "warehouse_code",
        "in": "query",
        "type": "string",
        "required": false
    },
    {
        "name": "simple_skus",
        "in": "query",
        "type": "array",
        "items": {
            "type": "string"
        },
        "collectionFormat": "multi",
        "required": true
    },
    {
        "name": "seller_id",
        "in": "query",
        "type": "integer",
        "format": "int64",
        "required": false
    },
    {
        "name": "oms_code",
        "in": "query",
        "type": "string",
        "required": false
    }
]
JSON;

        $newJson = <<<'JSON'
[
    {
        "name": "warehouse_code",
        "in": "query",
        "type": "string64",
        "x-go-name": "WarehouseCode",
        "x-go-type": "string"
    },
    {
        "name": "oms_code",
        "in": "query",
        "type": "string",
        "x-go-name": "OmsCode",
        "x-go-type": "string"
    },
    {
        "name": "simple_skus",
        "in": "query",
        "type": "array",
        "required": true,
        "items": {
            "type": "string"
        },
        "collectionFormat": "multi",
        "x-go-name": "SimpleSKUs",
        "x-go-type": "[]string"
    },
    {
        "name": "seller_id",
        "in": "query",
        "type": "integer",
        "format": "int64",
        "x-go-name": "SellerID",
        "x-go-type": "uint64"
    }
]
JSON;

        $expectedJson = <<<'JSON'
[
    {
        "name": "warehouse_code",
        "in": "query",
        "type": "string64",
        "x-go-name": "WarehouseCode",
        "x-go-type": "string"
    },
    {
        "name": "simple_skus",
        "in": "query",
        "type": "array",
        "items": {
            "type": "string"
        },
        "collectionFormat": "multi",
        "required": true,
        "x-go-name": "SimpleSKUs",
        "x-go-type": "[]string"
    },
    {
        "name": "seller_id",
        "in": "query",
        "type": "integer",
        "format": "int64",
        "x-go-name": "SellerID",
        "x-go-type": "uint64"
    },
    {
        "name": "oms_code",
        "in": "query",
        "type": "string",
        "x-go-name": "OmsCode",
        "x-go-type": "string"
    }
]
JSON;

        $m = new JsonDiff(json_decode($oldJson), json_decode($newJson), JsonDiff::REARRANGE_ARRAYS);
        $this->assertSame($expectedJson, json_encode($m->getRearranged(), JSON_PRETTY_PRINT));
    }

    function testRearrangeKeepOriginal()
    {
        $old = json_decode('[
          {
            "type": "string",
            "name": "qp1",
            "in": "query"
          },
          {
            "type": "string",
            "name": "qp",
            "in": "query"
          },
          {
            "name": "body",
            "in": "body",
            "schema": {
              "$ref": "#/definitions/UsecaseSampleInput"
            },
            "required": true
          }
        ]');

        $new = json_decode('[
          {
            "type": "string",
            "name": "qp1",
            "in": "query"
          },
          {
            "type": "string",
            "name": "qp2",
            "in": "query"
          },
          {
            "name": "body",
            "in": "body",
            "schema": {
              "$ref": "#/definitions/UsecaseSampleInput"
            },
            "required": true
          }
        ]');

        $m = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS);
        $this->assertSame(
            json_encode($new, JSON_PRETTY_PRINT),
            json_encode($m->getRearranged(), JSON_PRETTY_PRINT)
        );
    }

    public function testEqualItems()
    {
        $diff = new \Swaggest\JsonDiff\JsonDiff(
            json_decode('{"data": [{"A": 1, "C": [1,2,3]},{"B": 2}]}'),
            json_decode('{"data": [{"B": 2},{"A": 1, "C": [3,2,1]}]}'),
            JsonDiff::REARRANGE_ARRAYS);

        $this->assertEmpty($diff->getDiffCnt());
    }

    public function testEqualItemsDiff()
    {
        $diff = new \Swaggest\JsonDiff\JsonDiff(
            json_decode('{"data": [{"A": 1, "C": [1,2,3,4]},{"B": 2}]}'),
            json_decode('{"data": [{"B": 2},{"A": 1, "C": [5,3,2,1]}]}'),
            JsonDiff::REARRANGE_ARRAYS);

        $this->assertEquals('[{"value":4,"op":"test","path":"/data/0/C/3"},{"value":5,"op":"replace","path":"/data/0/C/3"}]',
            json_encode($diff->getPatch(), JSON_UNESCAPED_SLASHES));
    }
}
