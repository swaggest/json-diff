<?php

namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Cli\App;
use Swaggest\JsonDiff\Cli\Apply;
use Swaggest\JsonDiff\Cli\Diff;
use Swaggest\JsonDiff\Cli\Info;
use Swaggest\JsonDiff\Cli\Rearrange;
use Yaoi\Cli\Command\Application\Runner;
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
        $this->assertSame(
            file_get_contents(__DIR__ . '/../../tests/assets/rearranged.json'),
            str_replace("\r", '', $res)
        );

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
        $this->assertSame(
            file_get_contents(__DIR__ . '/../../tests/assets/patch.json'),
            str_replace("\r", '', $res)
        );
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
        $this->assertSame(
            file_get_contents(__DIR__ . '/../../tests/assets/rearranged.json'),
            str_replace("\r", '', $res)
        );
    }

    public function testInfo()
    {
        $d = new Info();
        $d->pretty = true;
        $d->rearrangeArrays = true;
        $d->withContents = true;
        $d->withPaths = true;
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
    "removedCnt": 3,
    "addedPaths": [
        "/key3/sub3",
        "/key4/1/c",
        "/key4/2/c",
        "/key5"
    ],
    "modifiedPaths": [
        "/key1/0",
        "/key3/sub1",
        "/key3/sub2"
    ],
    "removedPaths": [
        "/key2",
        "/key3/sub0",
        "/key4/1/b"
    ],
    "added": {
        "key3": {
            "sub3": 0
        },
        "key4": {
            "1": {
                "c": false
            },
            "2": {
                "c": 1
            }
        },
        "key5": "wat"
    },
    "modifiedNew": {
        "key1": [
            5
        ],
        "key3": {
            "sub1": "c",
            "sub2": false
        }
    },
    "modifiedOriginal": {
        "key1": [
            4
        ],
        "key3": {
            "sub1": "a",
            "sub2": "b"
        }
    },
    "removed": {
        "key2": 2,
        "key3": {
            "sub0": 0
        },
        "key4": {
            "1": {
                "b": false
            }
        }
    }
}

JSON
            , str_replace("\r", '', $res));
    }


    public function testApp()
    {
        ob_start();
        Runner::create(new App())->run();
        ob_end_clean();
    }

}