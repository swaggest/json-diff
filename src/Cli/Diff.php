<?php

namespace Swaggest\JsonDiff\Cli;


use Yaoi\Command;

class Diff extends Base
{
    public static function setUpDefinition(Command\Definition $definition, $options)
    {
        parent::setUpDefinition($definition, $options);
        $definition->description = 'Make patch from two json documents, output to STDOUT';
    }


    public function performAction()
    {
        $this->prePerform();
        if (null === $this->diff) {
            return;
        }

        $this->out = $this->diff->getPatch();

        $this->postPerform();
    }
}