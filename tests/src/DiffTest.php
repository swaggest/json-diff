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

}