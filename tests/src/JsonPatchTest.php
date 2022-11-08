<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\InvalidFieldTypeException;
use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;
use Swaggest\JsonDiff\JsonPatch\OpPath;
use Swaggest\JsonDiff\MissingFieldException;
use Swaggest\JsonDiff\PatchTestOperationFailedException;
use Swaggest\JsonDiff\PathException;
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

    /**
     * @dataProvider provideInvalidFieldType
     *
     * @param object $operation
     * @param string $expectedMessage
     * @param string $expectedField
     * @param string $expectedType
     */
    public function testInvalidFieldType($operation, $expectedMessage, $expectedField, $expectedType)
    {
        try {
            JsonPatch::import(array($operation));
            $this->fail('Expected exception was not thrown');
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidFieldTypeException::class, $exception);
            $this->assertSame($expectedMessage, $exception->getMessage());
            $this->assertSame($expectedField, $exception->getField());
            $this->assertSame($expectedType, $exception->getExpectedType());
            $this->assertSame($operation, $exception->getOperation());
        }
    }

    public function provideInvalidFieldType()
    {
        return [
            '"op" invalid type' => [
                (object)array('op' => array('foo' => 'bar'), 'path' => '/123', 'value' => 'test'),
                'Invalid field type - "op" should be of type: string',
                'op',
                'string'
            ],
            '"path" invalid type' => [
                (object)array('op' => 'add', 'path' => array('foo' => 'bar'), 'value' => 'test'),
                'Invalid field type - "path" should be of type: string',
                'path',
                'string'
            ],
            '"from" invalid type' => [
                (object)array('op' => 'move', 'path' => '/123', 'from' => array('foo' => 'bar')),
                'Invalid field type - "from" should be of type: string',
                'from',
                'string'
            ]
        ];
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
        $actualValue = 'xyz';
        $data = array('abc' => $actualValue);
        $operation = new JsonPatch\Test('/abc', 'def');

        $p = new JsonPatch();
        $p->op($operation);
        $testError = $p->apply($data, false)[0];
        $this->assertInstanceOf(PatchTestOperationFailedException::class, $testError);
        $this->assertSame($operation, $testError->getOperation());
        $this->assertSame($actualValue, $testError->getActualValue());
    }

    public function testPathExceptionContinueOnError()
    {
        $actualValue = 'xyz';
        $data = array('abc' => $actualValue);
        $patch = new JsonPatch();

        $operation1 = new JsonPatch\Test('/abc', 'def');
        $patch->op($operation1);

        $operation2 = new JsonPatch\Move('/target', '/source');
        $patch->op($operation2);

        $errors = $patch->apply($data, false);

        $this->assertInstanceOf(PatchTestOperationFailedException::class, $errors[0]);
        $this->assertSame($operation1, $errors[0]->getOperation());

        $this->assertInstanceOf(PathException::class, $errors[1]);
        $this->assertSame($operation2, $errors[1]->getOperation());
        $this->assertSame('from', $errors[1]->getField());
    }

    public function pathExceptionProvider() {
        return [
            'splitPath_path' => [
                new JsonPatch\Copy('invalid/path', '/valid/from'),
                'Path must start with "/": invalid/path',
                'path'
            ],
            'splitPath_from' => [
                new JsonPatch\Copy('/valid/path', 'invalid/from'),
                'Path must start with "/": invalid/from',
                'from'
            ],
            'add' => [
                new JsonPatch\Add('/some/path', 22),
                'Non-existent path item: some',
                'path'
            ],
            'get_from' => [
                new JsonPatch\Copy('/target', '/source'),
                'Key not found: source',
                'from'
            ],
            'get_path' => [
                new JsonPatch\Replace('/some/path', 23),
                'Key not found: some',
                'path'
            ],
            'remove_from' => [
                new JsonPatch\Move('/target', '/source'),
                'Key not found: source',
                'from'
            ],
            'remove_path' => [
                new JsonPatch\Remove('/some/path'),
                'Key not found: some',
                'path'
            ]
        ];
    }

    /**
     * @param OpPath $operation
     * @param string $expectedMessage
     * @param string $expectedField
     *
     * @dataProvider pathExceptionProvider
     */
    public function testPathException(OpPath $operation, $expectedMessage, $expectedField) {
        $data = new \stdClass();
        $patch = new JsonPatch();

        $patch->op($operation);

        try {
            $patch->apply($data );
            $this->fail('PathException expected');
        } catch (Exception $ex) {
            $this->assertInstanceOf(PathException::class, $ex);
            $this->assertEquals($expectedMessage, $ex->getMessage());
            $this->assertEquals($expectedField, $ex->getField());
        }
    }
}
