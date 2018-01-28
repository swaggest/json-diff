<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonPatch;

class SpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider specProvider
     */
    public function testSpec($case)
    {
        $comment = $case->comment;
        $doc = clone $case->doc;
        $patch = $case->patch;
        $expected = isset($case->expected) ? $case->expected : null;
        $error = isset($case->error) ? $case->error : null;

        try {
            $patch = JsonPatch::import($patch);
            $patch->apply($doc);
            if ($error !== null) {
                $this->fail('Error expected: ' . $error
                    . "\n" . json_encode($case, JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));
            }
            $this->assertEquals($expected, $doc, json_encode($case, JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            $hasException = true;
            if ($error === null) {
                $this->fail($e->getMessage()
                    . "\n" . json_encode($case, JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT));
            }
        }
    }

    public function specProvider()
    {
        $cases = json_decode(file_get_contents(__DIR__ . '/../assets/spec-tests.json'));

        $testCases = array();
        foreach ($cases as $case) {
            $comment = $case->comment;

            $testCases[$comment] = array(
                'case' => $case,
            );
        }
        return $testCases;
    }


}