<?php

namespace Swaggest\JsonDiff\Tests\Issues;

use Swaggest\JsonDiff\JsonDiff;

class Issue14Test extends \PHPUnit_Framework_TestCase
{
    public function testIssue()
    {
        new JsonDiff(
            ["name" => "Test"],
            [],
            JsonDiff::REARRANGE_ARRAYS
        );
    }

}