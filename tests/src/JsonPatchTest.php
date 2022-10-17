<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;
use Swaggest\JsonDiff\MissingFieldException;
use Swaggest\JsonDiff\PatchTestOperationFailedException;
use Swaggest\JsonDiff\UnknownOperationException;

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

    public function testMissingOp()
    {
		$operation = (object)array('path' => '/123');
		try {
			JsonPatch::import(array($operation));
			$this->fail('Expected exception was not thrown');
		} catch (Exception $exception) {
			$this->assertInstanceOf(MissingFieldException::class, $exception);
			$this->assertSame('Missing "op" in operation data', $exception->getMessage());
			$this->assertSame('op', $exception->getMissingField());
			$this->assertSame($operation, $exception->getOperation());
		}
    }

    public function testMissingPath()
    {
        $this->setExpectedException(get_class(new Exception()), 'Missing "path" in operation data');
        JsonPatch::import(array((object)array('op' => 'wat')));
    }

    public function testInvalidOp()
    {
		$operation = (object)array('op' => 'wat', 'path' => '/123');
		try {
			JsonPatch::import(array($operation));
			$this->fail('Expected exception was not thrown');
		} catch (Exception $exception) {
			$this->assertInstanceOf(UnknownOperationException::class, $exception);
			$this->assertSame('Unknown "op": wat', $exception->getMessage());
			$this->assertSame($operation, $exception->getOperation());
		}
    }

    public function testMissingFrom()
    {
        $this->setExpectedException(get_class(new Exception()), 'Missing "from" in operation data');
        JsonPatch::import(array((object)array('op' => 'copy', 'path' => '/123')));
    }

    public function testMissingValue()
    {
        $this->setExpectedException(get_class(new Exception()), 'Missing "value" in operation data');
        JsonPatch::import(array(array('op' => 'add', 'path' => '/123')));
    }

    public function testApply()
    {
        $p = JsonPatch::import(array(array('op' => 'copy', 'path' => '/1', 'from' => '/0')));
        $original = array('AAA');
        $p->apply($original);
        $this->assertSame(array('AAA', 'AAA'), $original);
    }

    public function testApplyContinueOnError()
    {
        $p = new JsonPatch();
        $p->op(new JsonPatch\Test('/missing', 1));
        $p->op(new JsonPatch\Copy('/1', '/0'));
        $p->op(new JsonPatch\Test('/missing2', null));
        $original = array('AAA');
        $errors = $p->apply($original, false);
        $this->assertSame(array('AAA', 'AAA'), $original);
        $this->assertSame('Key not found: missing', $errors[0]->getMessage());
        $this->assertSame('Key not found: missing2', $errors[1]->getMessage());
    }


    public function testApplyNonExistentLevelTwo()
    {
        $data = new \stdClass();
        $p = new JsonPatch();
        $p->op(new JsonPatch\Add('/some/path', 22));
        $p->apply($data, false);
        $this->assertEquals(new \stdClass(), $data);
    }

    public function testApplyNonExistentLevelOne()
    {
        $data = new \stdClass();
        $p = new JsonPatch();
        $p->op(new JsonPatch\Add('/some', 22));
        $p->apply($data);
        $this->assertEquals((object)array('some' => 22), $data);
    }

    public function testTestOperationFailed()
    {
        $data = array('abc' => 'xyz');
        $p = new JsonPatch();
        $p->op(new JsonPatch\Test('/abc', 'def'));
        $errors = $p->apply($data, false);
        $this->assertInstanceOf(PatchTestOperationFailedException::class, $errors[0]);
    }

}
