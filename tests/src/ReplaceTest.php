<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonValueReplace;

class ReplaceTest extends \PHPUnit_Framework_TestCase
{
    public function testReplace()
    {
        $data = json_decode(<<<JSON
{
    "data": [
        {"a":"b","c":"d"},
        {"c":"d", "a":"b"},
        {"c":"d"}
    ],
    "o":{"a":"b","c":"d"}
}
JSON
        );
        $replace = new JsonValueReplace(
            json_decode('{"a":"b","c":"d"}'),
            json_decode('{"a":"b","c":"d","e":"f"}')
        );

        $result = $replace->process($data);
        $expected = json_decode(<<<JSON
{
    "data": [
        {"a":"b","c":"d","e":"f"},
        {"c":"d", "a":"b","e":"f"},
        {"c":"d"}
    ],
    "o":{"a":"b","c":"d","e":"f"}
}
JSON
        );

        $this->assertEquals($expected, $result);
    }

    public function testReplaceFilterPath()
    {
        $data = json_decode(<<<JSON
{
    "data": [
        {"a":"b","c":"d"},
        {"c":"d", "a":"b"},
        {"c":"d"}
    ],
    "o":{"a":"b","c":"d"}
}
JSON
        );
        $replace = new JsonValueReplace(
            json_decode('{"a":"b","c":"d"}'),
            json_decode('{"a":"b","c":"d","e":"f"}'),
            '~.*/data/.*~'
        );

        $result = $replace->process($data);
        $expected = json_decode(<<<JSON
{
    "data": [
        {"a":"b","c":"d","e":"f"},
        {"c":"d", "a":"b","e":"f"},
        {"c":"d"}
    ],
    "o":{"a":"b","c":"d"}
}
JSON
        );

        $this->assertEquals($expected, $result);

        $this->assertSame(array('/data/0', '/data/1'), $replace->affectedPaths);
    }


    public function testReplaceScalar()
    {
        $data = json_decode(<<<JSON
{
    "data": [
        {"a":"b","c":"d"},
        {"c":"d", "a":"b"},
        {"c":"d"}
    ],
    "o":{"a":"b","c":"d"}
}
JSON
        );
        $replace = new JsonValueReplace("b", "B");

        $result = $replace->process($data);
        $expected = json_decode(<<<JSON
{
    "data": [
        {"a":"B","c":"d"},
        {"c":"d", "a":"B"},
        {"c":"d"}
    ],
    "o":{"a":"B","c":"d"}
}
JSON
        );

        $this->assertEquals($expected, $result);
    }

}