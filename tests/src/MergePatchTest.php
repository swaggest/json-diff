<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonMergePatch;

class MergePatchTest extends \PHPUnit_Framework_TestCase
{

    public function testSimple()
    {
        $case = json_decode(<<<'JSON'
  {
    "doc": {},
    "patch": {
      "a": {
        "bb": {
          "ccc": null
        }
      }
    },
    "expected": {
      "a": {
        "bb": {}
      }
    }
  }
JSON
        );
        $doc = $case->doc;
        $patch = $case->patch;
        $expected = $case->expected;

        JsonMergePatch::apply($doc, $patch);

        $this->assertEquals($expected, $doc);
    }

    public function test13()
    {
        $case = json_decode(<<<'JSON'
{
    "doc": [
        1,
        2
    ],
    "patch": {
        "a": "b",
        "c": null
    },
    "expected": {
        "a": "b"
    }
}
JSON
        );
        $doc = $case->doc;
        $patch = $case->patch;
        $expected = $case->expected;

        JsonMergePatch::apply($doc, $patch);

        $this->assertEquals($expected, $doc);

    }

    public function testGetMergePatch()
    {
        $case = json_decode(<<<'JSON'
{
    "doc": {
        "a": {
            "b": "c"
        }
    },
    "patch": {
        "a": {
            "b": "d",
            "c": null
        }
    },
    "expected": {
        "a": {
            "b": "d"
        }
    }
}
JSON
        );
        $doc = $case->doc;
        $expected = $case->expected;
        $diff = new JsonDiff($doc, $expected);
        $mergePatch = $diff->getMergePatch();
        JsonMergePatch::apply($doc, $mergePatch);

        $this->assertEquals($expected, $doc, 'Apply created failed: ' . json_encode(
                [
                    "test" => $case,
                    "patch" => $mergePatch,
                    "result" => $doc,
                ], JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));

    }


    public function testGetMergePatchOfArray()
    {
        $case = json_decode(<<<'JSON'
{
    "doc": {
        "a": "b"
    },
    "patch": [
        "c"
    ],
    "expected": [
        "c"
    ]
}
JSON
        );
        $doc = $case->doc;
        $expected = $case->expected;
        $diff = new JsonDiff($doc, $expected);
        $mergePatch = $diff->getMergePatch();
        JsonMergePatch::apply($doc, $mergePatch);

        $this->assertEquals($expected, $doc, 'Apply created failed: ' . json_encode(
                [
                    "test" => $case,
                    "patch" => $mergePatch,
                    "result" => $doc,
                ], JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));

    }


    public function testSame()
    {
        $case = json_decode(<<<'JSON'
{
    "doc": {
        "a": {
            "b": "d"
        }
    },
    "patch": {
        "a": {
            "b": "d"
        }
    },
    "expected": {
        "a": {
            "b": "d"
        }
    }
}
JSON
        );
        $doc = $case->doc;
        $expected = $case->expected;
        $diff = new JsonDiff($doc, $expected);
        //print_r($diff->getPatch());
        $mergePatch = $diff->getMergePatch();
        JsonMergePatch::apply($doc, $mergePatch);

        $this->assertEquals($expected, $doc, 'Apply created failed: ' . json_encode(
                [
                    "test" => $case,
                    "patch" => $mergePatch,
                    "result" => $doc,
                ], JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));

    }

    public function testArraySwap()
    {
        $case = json_decode(<<<'JSON'
{
    "doc": [
        "a",
        "b"
    ],
    "patch": [
        "c",
        "d"
    ],
    "expected": [
        "c",
        "d"
    ]
}
JSON
        );
        $doc = $case->doc;
        $expected = $case->expected;
        $diff = new JsonDiff($doc, $expected);
        //print_r($diff->getPatch());
        $mergePatch = $diff->getMergePatch();
        JsonMergePatch::apply($doc, $mergePatch);

        $this->assertEquals($expected, $doc, 'Apply created failed: ' . json_encode(
                [
                    "test" => $case,
                    "patch" => $mergePatch,
                    "result" => $doc,
                ], JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));


    }


    /**
     * @dataProvider specTestsProvider
     */
    public function testSpec($case)
    {
        $this->doTest($case);
    }

    public function specTestsProvider()
    {
        return $this->provider(__DIR__ . '/../assets/merge-patch.json');
    }

    protected function provider($path)
    {
        $cases = json_decode(file_get_contents($path));

        $testCases = array();
        foreach ($cases as $i => $case) {
            if (!isset($case->comment)) {
                $comment = 'unknown' . $i;
            } else {
                $comment = $case->comment;
            }

            $testCases[$comment] = array(
                'case' => $case,
            );
        }
        return $testCases;
    }

    protected function doTest($case)
    {
        $case = clone $case;

        if (!is_object($case->doc)) {
            $doc = $case->doc;
        } else {
            $doc = clone $case->doc;
        }
        $patch = $case->patch;
        $expected = isset($case->expected) ? $case->expected : null;
        $jsonOptions = JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT;


        JsonMergePatch::apply($doc, $patch);
        $this->assertEquals($expected, $doc, 'Apply failed: ' . json_encode(
                [
                    "test" => $case,
                    "result" => $doc,
                ], $jsonOptions));

        if (!is_object($case->doc)) {
            $doc = $case->doc;
        } else {
            $doc = clone $case->doc;
        }
        try {
            $diff = new JsonDiff($doc, $expected);
            $mergePatch = $diff->getMergePatch();
        } catch (Exception $exception) {
            $mergePatch = $exception->getMessage();
        }
        JsonMergePatch::apply($doc, $mergePatch);

        $this->assertEquals($expected, $doc, 'Apply created failed: ' . json_encode(
                [
                    "test" => $case,
                    "patch" => $mergePatch,
                    "result" => $doc,
                ], $jsonOptions));


    }

    public function testComplex()
    {
        $original = json_decode(file_get_contents(__DIR__ . '/../assets/original.json'));
        $new = json_decode(file_get_contents(__DIR__ . '/../assets/new.json'));

        $diff = new JsonDiff($original, $new);
        $mergePatch = $diff->getMergePatch();
        $mergePatchJson = json_encode($mergePatch, JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT);

        $this->assertEquals(file_get_contents(__DIR__ . '/../assets/merge.json') , $mergePatchJson);

        JsonMergePatch::apply($original, $mergePatch);
        $this->assertEquals($new, $original);
    }


}