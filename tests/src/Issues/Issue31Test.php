<?php

namespace Swaggest\JsonDiff\Tests\Issues;

use Swaggest\JsonDiff\JsonPatch;

class Issue31Test extends \PHPUnit_Framework_TestCase
{
    public function testIssue()
    {
        $reportData = json_decode('{}');
        $patch = JsonPatch::import(json_decode('[{"op":"add","path":"","value":["a","b","c","d"]},{"op":"remove","path":"\/3","value":""},{"op":"add","path":"\/-","value":"e"}]'));
        $patch->apply($reportData);

        $this->assertSame('["a","b","c","e"]', json_encode($reportData));
    }

}