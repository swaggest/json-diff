<?php
namespace Swaggest\JsonDiff\Tests;

use Swaggest\JsonDiff\Cli\Diff;
use Yaoi\Command\Definition;

class CliTest extends \PHPUnit_Framework_TestCase
{
    public function testCli()
    {
        Diff::setUpDefinition(new Definition(), new \stdClass());

        $d = new Diff();
        $d->originalPath = __DIR__ . '/../../composer.json';
        $d->newPath = __DIR__ . '/../../composer.json';

        $d->action = Diff::ACTION_CHANGES;
        $d->performAction();

        $d->action = Diff::ACTION_REARRANGE;
        $d->performAction();

        $d->action = Diff::ACTION_ADDITIONS;
        $d->performAction();

        $d->action = Diff::ACTION_MODIFICATIONS;
        $d->performAction();

        $d->action = Diff::ACTION_REMOVALS;
        $d->performAction();
    }


}