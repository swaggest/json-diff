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

        $this->assertJsonStringEqualsJsonString('[{"value":4,"op":"test","path":"/data/0/C/3"},{"value":5,"op":"replace","path":"/data/0/C/3"}]',
            json_encode($diff->getPatch(), JSON_UNESCAPED_SLASHES));
    }

    public function testExample()
    {
        $diff = new \Swaggest\JsonDiff\JsonDiff(
            json_decode('[{"name": "Alex", "height": 180},{"name": "Joe", "height": 179},{"name": "Jane", "height": 165}]'),
            json_decode('[{"name": "Joe", "height": 179},{"name": "Jane", "height": 168},{"name": "Alex", "height": 180}]'),
            JsonDiff::REARRANGE_ARRAYS);

        $this->assertJsonStringEqualsJsonString('[{"value":165,"op":"test","path":"/2/height"},{"value":168,"op":"replace","path":"/2/height"}]',
            json_encode($diff->getPatch(), JSON_UNESCAPED_SLASHES));
    }

    public function testReplacement()
    {
        $ex1 = json_decode(<<<'JSON'
{
  "attribute": {
    "name": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "attribute": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "dimension": ".UpwardPropagation - Prescriptions",
    "object": "Patients"
  },
  "selectedStates": [
    "]200,500]",
    "]500,1000]",
    "]20,50]",
    "]100,200]",
    "]5000,10000]",
    "]5,10]",
    "]1,2]",
    "]10,20]",
    "null",
    "]10000,oo[",
    "]2,5]",
    "]0,1]",
    "]1000,2000]",
    "]50,100]",
    "]2000,5000]"
  ]
}
JSON
        );

        $ex2 = json_decode(<<<'JSON'
{
  "attribute": {
    "name": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "attribute": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "dimension": ".UpwardPropagation - Prescriptions",
    "object": "Patients"
  },
  "selectedStates": [
    "]2000,5000]",
    "]2,5]",
    "]20,50]",
    "]1,2]",
    "]10000,oo[",
    "]200,500]",
    "]50,100]",
    "]500,1000]",
    "]5,10]",
    "]10,20]",
    "null",
    "]0,1]",
    "]1000,2000]",
    "]5000,10000]",
    "]100,200]"
  ]
}
JSON
        );

        $diff = new JsonDiff($ex1, $ex2, JsonDiff::REARRANGE_ARRAYS);
        $ex2r = $diff->getRearranged();
        $missingItems = [];
        foreach ($ex2->selectedStates as $i => $item) {
            if (!in_array($item, $ex2r->selectedStates)) {
                $missingItems[$i] = $item;
            }
        }

        $this->assertEmpty($missingItems, json_encode($ex2r, JSON_UNESCAPED_SLASHES));
        $this->assertEquals(
            json_encode($ex1, JSON_UNESCAPED_SLASHES+JSON_PRETTY_PRINT),
            json_encode($ex2r, JSON_UNESCAPED_SLASHES+JSON_PRETTY_PRINT)
        );
    }

    public function testReplacementChanges()
    {
        $ex1 = json_decode(<<<'JSON'
{
  "attribute": {
    "name": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "attribute": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "dimension": ".UpwardPropagation - Prescriptions",
    "object": "Patients"
  },
  "selectedStates": [
    "]200,500]",
    "]500,1000]",
    "]100,200]",
    "]5000,10000]",
    "]5,10]",
    "]1,2]",
    "]10,20]",
    "null",
    "]10000,oo[",
    "]2,5]",
    "]0,1]",
    "]1000,2000]",
    "]50,100]",
    "]2000,5000]"
  ]
}
JSON
        );

        $ex2 = json_decode(<<<'JSON'
{
  "attribute": {
    "name": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "attribute": ".UpwardPropagation - Prescriptions - Log-Ranges",
    "dimension": ".UpwardPropagation - Prescriptions",
    "object": "Patients"
  },
  "selectedStates": [
    "]2000,5000]",
    "]2,5]",
    "]20,50]",
    "]1,2]",
    "]10000,oo[",
    "]200,500]",
    "]50,100]",
    "]500,1000]",
    "]5,10]",
    "]10,20]",
    "]0,1]",
    "]1000,2000]",
    "]5000,10000]",
    "]100,200]"
  ]
}
JSON
        );

        $diff = new JsonDiff($ex1, $ex2, JsonDiff::REARRANGE_ARRAYS);
        $ex2r = $diff->getRearranged();
        $missingItems = [];
        foreach ($ex2->selectedStates as $i => $item) {
            if (!in_array($item, $ex2r->selectedStates)) {
                $missingItems[$i] = $item;
            }
        }

        $this->assertEmpty($missingItems, json_encode($ex2r, JSON_UNESCAPED_SLASHES));
        $this->assertEquals(
            '{
    "attribute": {
        "name": ".UpwardPropagation - Prescriptions - Log-Ranges",
        "attribute": ".UpwardPropagation - Prescriptions - Log-Ranges",
        "dimension": ".UpwardPropagation - Prescriptions",
        "object": "Patients"
    },
    "selectedStates": [
        "]200,500]",
        "]500,1000]",
        "]100,200]",
        "]5000,10000]",
        "]5,10]",
        "]1,2]",
        "]10,20]",
        "]20,50]",
        "]10000,oo[",
        "]2,5]",
        "]0,1]",
        "]1000,2000]",
        "]50,100]",
        "]2000,5000]"
    ]
}',
            json_encode($ex2r, JSON_UNESCAPED_SLASHES+JSON_PRETTY_PRINT)
        );
    }

    public function testStripFirst() {
        $old = json_decode('{"my_array":[{"key":"qwerty"},{"key":"asdfg"},{"key":"zxcvb"}]}');
        $new = json_decode('{"my_array":[{"key":"asdfg"},{"key":"zxcvb"}]}');

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[{"op": "remove","path": "/my_array/0"}]', json_encode($patch));
        $patch->apply($old);

        $this->assertEquals($old, $new);
    }

    public function testStripLast() {
        $old = json_decode('{"my_array":[{"key":"qwerty"},{"key":"asdfg"},{"key":"zxcvb"}]}');
        $new = json_decode('{"my_array":[{"key":"qwerty"},{"key":"asdfg"}]}');

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[{"op": "remove","path": "/my_array/2"}]', json_encode($patch));
        $patch->apply($old);

        $this->assertEquals($old, $new);
    }

    public function testStripMid() {
        $old = json_decode('{"my_array":[{"key":"qwerty"},{"key":"asdfg"},{"key":"zxcvb"}]}');
        $new = json_decode('{"my_array":[{"key":"qwerty"},{"key":"zxcvb"}]}');

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[{"op": "remove","path": "/my_array/1"}]', json_encode($patch));
        $patch->apply($old);

        $this->assertEquals($old, $new);
    }

    public function testStrings() {
        $old = json_decode('{"my_array":[
           "]5,10]","]50,100]","]1000,2000]","]10000,oo[","]10,20]","]500,1000]","]20,50]","]2,5]",
           "]1,2]","]2000,5000]","]5000,10000]","]100,200]","]0,1]","null","]200,500]"
          ]}');

        $new = json_decode('{"my_array":[
           "]5,10]","]50,100]","]1000,2000]","]10000,oo[","]10,20]","]500,1000]","]20,50]","]2,5]",
           "]1,2]","]2000,5000]","]5000,10000]","]100,200]","]0,1]","null","]200,500]"
          ]}');

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[]', json_encode($patch));
        $patch->apply($old);

        $this->assertEquals($old, $new);
    }

    public function testStrings2() {
        $old = json_decode('{"my_array":[
       "null","]5,10]","]20,50]","]2000,5000]","]500,1000]","]10,20]","]0,1]","]1000,2000]",
       "]10000,oo[","]1,2]","]100,200]","]50,100]","]5000,10000]","]2,5]","]200,500]"
      ]}');

        $new = json_decode('{"my_array":[
       "]5,10]","]50,100]","]1000,2000]","]10000,oo[","]10,20]","]500,1000]","]20,50]","]2,5]",
       "]1,2]","]2000,5000]","]5000,10000]","]100,200]","]0,1]","null","]200,500]"
      ]}');

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[]', json_encode($patch));
    }


    public function testComplex() {
        $old = json_decode(file_get_contents(__DIR__ . '/../assets/issue38_1.json'));
        $new = json_decode(file_get_contents(__DIR__ . '/../assets/issue38_2.json'));

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[]', json_encode($patch));
    }

    public function testNestedObjects() {
        $old = json_decode('{"my_array":[
           {"a":{"a1":1,"b1":1}}, 
           {"a":{"a2":2,"b2":2}}
          ]}');

        $new = json_decode('{"my_array":[
           {"a":{"a2":2,"b2":2}},
           {"a":{"a1":1,"b1":1}} 
          ]}');

        $diff = new JsonDiff($old, $new, JsonDiff::REARRANGE_ARRAYS );
        $patch = $diff->getPatch();

        $this->assertJsonStringEqualsJsonString('[]', json_encode($patch));
    }

}
