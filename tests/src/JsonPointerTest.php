<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonPointer;
use Swaggest\JsonDiff\JsonPointerException;

class JsonPointerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws Exception
     */
    public function testProcess()
    {
        $json = new \stdClass();
        JsonPointer::add($json, ['l1', 'l2', 'l3'], 'hello!');
        $this->assertSame('{"l1":{"l2":{"l3":"hello!"}}}', json_encode($json));

        JsonPointer::add($json, ['l1', 'l2', 'l3'], 'hello again!', JsonPointer::SKIP_IF_ISSET);
        $this->assertSame('{"l1":{"l2":{"l3":"hello!"}}}', json_encode($json));

        JsonPointer::add($json, ['l1', 'l2', 'l3'], 'hello again!');
        $this->assertSame('{"l1":{"l2":{"l3":"hello again!"}}}', json_encode($json));

        JsonPointer::add($json, ['l1', 'l2', 'l3'], 'hello!');
        $this->assertSame('{"l1":{"l2":{"l3":"hello!"}}}', json_encode($json));

        $this->assertSame('{"l3":"hello!"}', json_encode(JsonPointer::get($json, JsonPointer::splitPath('/l1/l2'))));

        try {
            $this->assertSame('null', json_encode(JsonPointer::get($json, JsonPointer::splitPath('/l1/l2/non-existent'))));
        } catch (JsonPointerException $exception) {
            $this->assertSame('Key not found: non-existent', $exception->getMessage());
        }

        JsonPointer::remove($json, ['l1', 'l2']);
        $this->assertSame('{"l1":{}}', json_encode($json));

        JsonPointer::add($json, JsonPointer::splitPath('/l1/l2/0/0'), 0);
        JsonPointer::add($json, JsonPointer::splitPath('#/l1/l2/1/1'), 1);

        $this->assertSame('{"l1":{"l2":[[0],{"1":1}]}}', json_encode($json));

        $this->assertSame(1, JsonPointer::get($json, JsonPointer::splitPath('/l1/l2/1/1')));
        $this->assertSame(1, JsonPointer::getByPointer($json, '/l1/l2/1/1'));
    }

    /**
     * @throws Exception
     */
    public function testNumericKey()
    {
        $json = json_decode('{"l1":{"200":1}}');
        $this->assertSame(1, JsonPointer::get($json, JsonPointer::splitPath('/l1/200')));
    }


    public function testEscapeSegment()
    {
        $segment = '/project/{username}/{project}';
        $this->assertSame('~1project~1%7Busername%7D~1%7Bproject%7D', JsonPointer::escapeSegment($segment, true));
    }

    public function testBuildPath()
    {
        $pathItems = ['key1', '/project/{username}/{project}', 'key2'];

        $this->assertSame('/key1/~1project~1{username}~1{project}/key2',
            JsonPointer::buildPath($pathItems));
        $this->assertSame('#/key1/~1project~1%7Busername%7D~1%7Bproject%7D/key2',
            JsonPointer::buildPath($pathItems, true));
    }

    public function testGetSetDeleteObject()
    {
        $s = new Sample();
        $s->one = new Sample();
        $s->one->two = 2;

        $this->assertEquals(2, JsonPointer::get($s, ['one', 'two']));


        JsonPointer::add($s, ['one', 'two'], 22);
        $this->assertEquals(22, JsonPointer::get($s, ['one', 'two']));
        $this->assertEquals(22, $s->one->two);

        JsonPointer::remove($s, ['one', 'two']);
        try {
            JsonPointer::get($s, ['one', 'two']);
            $this->fail('Exception expected');
        } catch (JsonPointerException $e) {
            $this->assertEquals('Key not found: two', $e->getMessage());
        }
        $this->assertEquals(null, $s->one->two);
    }

    public function testSequentialArrayIsPreserved()
    {
		$json = [ 'l1' => [ 0 => 'foo', 1 => 'bar', 2 => 'baz' ] ];
		JsonPointer::remove($json, ['l1', '1'], JsonPointer::TOLERATE_ASSOCIATIVE_ARRAYS);
		$this->assertSame('{"l1":["foo","baz"]}', json_encode($json));
    }

}

class Sample
{
    public $declared;

    private $_data = [];

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function &__get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        } else {
            $tmp = null;
            return $tmp;;
        }
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }

}
