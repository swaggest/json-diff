<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonHash;

class JsonHashTest extends \PHPUnit_Framework_TestCase
{
    public function testHash()
    {
        $h1 = (new JsonHash())->xorHash(json_decode('{"data": [{"A": 1},{"B": 2}]}'));
        $h2 = (new JsonHash())->xorHash(json_decode('{"data": [{"B": 2},{"A": 1}]}'));
        $h3 = (new JsonHash())->xorHash(json_decode('{"data": [{"B": 3},{"A": 2}]}'));

        $this->assertNotEmpty($h1);
        $this->assertNotEmpty($h2);
        $this->assertNotEmpty($h3);
        $this->assertNotEquals($h1, $h2);
        $this->assertNotEquals($h1, $h3);
    }

    public function testHashRearrange()
    {
        $h1 = (new JsonHash(JsonDiff::REARRANGE_ARRAYS))
            ->xorHash(json_decode('{"data": [{"A1": 1},{"B1": 2}]}'));
        $h2 = (new JsonHash(JsonDiff::REARRANGE_ARRAYS))
            ->xorHash(json_decode('{"data": [{"B1": 2},{"A1": 1}]}'));
        $h3 = (new JsonHash(JsonDiff::REARRANGE_ARRAYS))
            ->xorHash(json_decode('{"data": [{"B1": 3},{"A1": 2}]}'));
        $h4 = (new JsonHash(JsonDiff::REARRANGE_ARRAYS))
            ->xorHash(json_decode('{"data": [{"B2": 2},{"A2": 1}]}'));

        $this->assertNotEmpty($h1);
        $this->assertNotEmpty($h2);
        $this->assertNotEmpty($h3);
        $this->assertNotEmpty($h4);
        $this->assertEquals($h1, $h2);
        $this->assertNotEquals($h1, $h3);
        $this->assertNotEquals($h1, $h4);
    }
}