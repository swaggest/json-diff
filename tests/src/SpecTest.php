<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonPatch;

/**
 * @see https://github.com/json-patch/json-patch-tests
 */
class SpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider specTestsProvider
     */
    public function testSpecTests($case)
    {
        $this->doTest($case);
    }

    /**
     * @dataProvider testsProvider
     */
    public function testTests($case)
    {
        $this->doTest($case);
    }


    public function testsProvider()
    {
        return $this->provider(__DIR__ . '/../assets/tests.json');
    }

    public function specTestsProvider()
    {
        return $this->provider(__DIR__ . '/../assets/spec-tests.json');
    }

    protected function provider($path)
    {
        $cases = json_decode(file_get_contents($path));

        $testCases = array();
        foreach ($cases as $i => $case) {
            if (!isset($case->comment)) {
                $comment = 'unknown' . $i;
            } else {
                $comment = $case->comment;
            }

            $testCases[$comment] = array(
                'case' => $case,
            );
        }
        return $testCases;
    }

    protected function doTest($case)
    {
        $case = clone $case;

        if (isset($case->disabled) && $case->disabled) {
            $this->markTestSkipped('test is disabled');
            return;
        }

        if (!is_object($case->doc)) {
            $doc = $case->doc;
        } else {
            $doc = clone $case->doc;
        }
        $patch = $case->patch;
        $hasExpected = array_key_exists('expected', (array)$case);
        $expected = isset($case->expected) ? $case->expected : null;
        $error = isset($case->error) ? $case->error : null;
        $jsonOptions = JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT;

        try {
            $patch = JsonPatch::import($patch);
            $patch->apply($doc);
            if ($error !== null) {
                $this->fail('Error expected: ' . $error
                    . "\n" . json_encode($case, $jsonOptions));
            }
            if ($hasExpected) {
                $this->assertEquals($expected, $doc, json_encode($case, $jsonOptions)
                    . "\n" . json_encode($doc, $jsonOptions));
            }
        } catch (Exception $e) {
            if ($e->getCode() === Exception::EMPTY_PROPERTY_NAME_UNSUPPORTED) {
                $this->markTestSkipped('Empty property name unsupported in PHP <7.1');
            }

            if ($error === null) {
                $this->fail($e->getMessage()
                    . "\n" . json_encode($case, $jsonOptions));
            }
        }
    }

}