<?php

namespace Swaggest\JsonDiff\Tests\Issues;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonDiff;
use Swaggest\JsonDiff\JsonPatch;

/**
 * @see https://github.com/swaggest/json-diff/issues/9
 */
class Issue9Test extends \PHPUnit_Framework_TestCase
{
    public function testPatchApply()
    {
        $old = json_decode(json_encode(["emptyObject" => []]));
        $new = json_decode(json_encode(["emptyObject" => ["notEmpty"=>"value"]]));
        $diff = new JsonDiff($old, $new);
        $patch = $diff->getPatch();
        $this->assertNotEquals($new, $old);
        $patch->apply($old);
        $this->assertEquals($new, $old);

        $old = json_decode(json_encode(["emptyObject" => []]));
        $this->setExpectedException(get_class(new Exception()), 'Invalid key for array operation');
        $patch->setFlags(JsonPatch::STRICT_MODE)->apply($old);
    }
} 
