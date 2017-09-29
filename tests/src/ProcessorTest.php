<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonProcessor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $json = new \stdClass();
        JsonProcessor::pushByPath($json, '#/l1/l2/l3', 'hello!');
        $this->assertSame('{"l1":{"l2":{"l3":"hello!"}}}', json_encode($json));

        $this->assertSame('{"l3":"hello!"}', json_encode(JsonProcessor::getByPath($json, '#/l1/l2')));

        try {
            $this->assertSame('null', json_encode(JsonProcessor::getByPath($json, '#/l1/l2/non-existent')));
        } catch (Exception $exception) {
            $this->assertSame('Key not found: non-existent', $exception->getMessage());
        }

        JsonProcessor::removeByPath($json, '#/l1/l2');
        $this->assertSame('{"l1":{}}', json_encode($json));

        JsonProcessor::pushByPath($json, '#/l1/l2/0/0', 0);
        JsonProcessor::pushByPath($json, '#/l1/l2/1/1', 1);

        $this->assertSame('{"l1":{"l2":[[0],{"1":1}]}}', json_encode($json));

        $this->assertSame(1, JsonProcessor::getByPath($json, '#/l1/l2/1/1'));
    }

    public function testNumericKey()
    {
        $json = json_decode('{"l1":{"200":1}}');
        $this->assertSame(1, JsonProcessor::getByPath($json, '#/l1/200'));
    }
}
