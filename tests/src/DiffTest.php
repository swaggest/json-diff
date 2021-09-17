<?php

namespace Swaggest\JsonDiff\Tests;


use Swaggest\JsonDiff\JsonDiff;

class DiffTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function testStopOnDiff()
    {
        $original = array(1, 2, 3, 4);
        $new = array(2, 4);
        $diff = new JsonDiff($original, $new, JsonDiff::STOP_ON_DIFF);
        $this->assertSame(1, $diff->getDiffCnt());
    }

    /**
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function testSkipPatch()
    {
        $original = (object)(array("root" => (object)array("a" => 1, "b" => 2)));
        $new = (object)(array("root" => (object)array("b" => 3, "c" => 4)));

        $diff = new JsonDiff($original, $new, JsonDiff::SKIP_JSON_PATCH);
        $this->assertSame(null, $diff->getPatch());
        $this->assertSame(3, $diff->getDiffCnt());
        $this->assertSame(1, $diff->getAddedCnt());
        $this->assertSame(1, $diff->getRemovedCnt());
        $this->assertSame(1, $diff->getModifiedCnt());

        $diff = new JsonDiff($original, $new, JsonDiff::REARRANGE_ARRAYS);
        $this->assertJsonStringEqualsJsonString('[{"op":"remove","path":"/root/a"},{"value":2,"op":"test","path":"/root/b"},{"value":3,"op":"replace","path":"/root/b"},{"value":4,"op":"add","path":"/root/c"}]',
            json_encode($diff->getPatch(), JSON_UNESCAPED_SLASHES));
        $this->assertSame(3, $diff->getDiffCnt());
        $this->assertSame(1, $diff->getAddedCnt());
        $this->assertSame(1, $diff->getRemovedCnt());
        $this->assertSame(1, $diff->getModifiedCnt());

    }

}