<?php

namespace Swaggest\JsonDiff\Cli;

use Yaoi\Command;

class Info extends Base
{
    public $withContents;
    public $withPaths;

    /**
     * @param Command\Definition $definition
     * @param \stdClass|static $options
     */
    static function setUpDefinition(Command\Definition $definition, $options)
    {
        parent::setUpDefinition($definition, $options);
        $options->withContents = Command\Option::create()->setDescription('Add content to output');
        $options->withPaths = Command\Option::create()->setDescription('Add paths to output');
        $definition->description = 'Show diff info for two JSON documents';
    }


    public function performAction()
    {
        $this->prePerform();

        $this->out = array(
            'addedCnt' => $this->diff->getAddedCnt(),
            'modifiedCnt' => $this->diff->getAddedCnt(),
            'removedCnt' => $this->diff->getRemovedCnt(),
        );
        if ($this->withPaths) {
            $this->out = array_merge($this->out, array(
                'addedPaths' => $this->diff->getAddedPaths(),
                'modifiedPaths' => $this->diff->getModifiedPaths(),
                'removedPaths' => $this->diff->getRemovedPaths(),
            ));
        }
        if ($this->withContents) {
            $this->out = array_merge($this->out, array(
                'added' => $this->diff->getAdded(),
                'modifiedNew' => $this->diff->getModifiedNew(),
                'modifiedOriginal' => $this->diff->getModifiedOriginal(),
                'removed' => $this->diff->getRemoved(),
            ));
        }
        $this->postPerform();
    }


}