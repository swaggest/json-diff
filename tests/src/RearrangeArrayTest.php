<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonDiff;

class RearrangeArrayTest extends \PHPUnit_Framework_TestCase
{
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

        $m = new JsonDiff(json_decode($oldJson), json_decode($newJson));
        $this->assertSame($expectedJson, json_encode($m->getRearranged(), JSON_PRETTY_PRINT));
    }

}
