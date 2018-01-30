<?php

namespace Swaggest\JsonDiff\Cli;


use Yaoi\Command;

class Rearrange extends Base
{
    public static function setUpDefinition(Command\Definition $definition, $options)
    {
        parent::setUpDefinition($definition, $options);
        $definition->description = 'Rearrange json document in the order of another (original) json document';
    }


    public function performAction()
    {
        $this->prePerform();
        if (null === $this->diff) {
            return;
        }

        $this->out = $this->diff->getRearranged();

        $this->postPerform();
    }

}