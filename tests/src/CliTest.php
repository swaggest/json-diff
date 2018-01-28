<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Cli\Apply;
use Swaggest\JsonDiff\Cli\Diff;
use Swaggest\JsonDiff\Cli\Info;
use Swaggest\JsonDiff\Cli\Rearrange;
use Yaoi\Cli\Response;

class CliTest extends \PHPUnit_Framework_TestCase
{
    public function testApply()
    {
        $d = new Apply();
        $d->pretty = true;
        $d->rearrangeArrays = true;
        $d->basePath = __DIR__ . '/../../tests/assets/original.json';
        $d->patchPath = __DIR__ . '/../../tests/assets/patch.json';
        $d->setResponse(new Response());
        ob_start();
        $d->performAction();
        $res = ob_get_clean();
        $this->assertSame(file_get_contents(__DIR__ . '/../../tests/assets/rearranged.json'), $res);

    }

    public function testDiff()
    {
        $d = new Diff();
        $d->pretty = true;
        $d->rearrangeArrays = true;
        $d->originalPath = __DIR__ . '/../../tests/assets/original.json';
        $d->newPath = __DIR__ . '/../../tests/assets/new.json';
        $d->setResponse(new Response());
        ob_start();
        $d->performAction();
        $res = ob_get_clean();
        $this->assertSame(file_get_contents(__DIR__ . '/../../tests/assets/patch.json'), $res);
    }

    public function testRearrange()
    {
        $d = new Rearrange();
        $d->pretty = true;
        $d->rearrangeArrays = true;
        $d->originalPath = __DIR__ . '/../../tests/assets/original.json';
        $d->newPath = __DIR__ . '/../../tests/assets/new.json';
        $d->setResponse(new Response());
        ob_start();
        $d->performAction();
        $res = ob_get_clean();
        $this->assertSame(file_get_contents(__DIR__ . '/../../tests/assets/rearranged.json'), $res);
    }

    public function testInfo()
    {
        $d = new Info();
        $d->pretty = true;
        $d->rearrangeArrays = true;
        $d->originalPath = __DIR__ . '/../../tests/assets/original.json';
        $d->newPath = __DIR__ . '/../../tests/assets/new.json';
        $d->setResponse(new Response());
        ob_start();
        $d->performAction();
        $res = ob_get_clean();
        $this->assertSame(<<<'JSON'
{
    "addedCnt": 4,
    "modifiedCnt": 4,
    "removedCnt": 3
}

JSON
            , $res);
    }


}