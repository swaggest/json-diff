<?php

namespace Swaggest\JsonDiff\Cli;

use Swaggest\JsonDiff\JsonDiff;
use Yaoi\Command;
use Yaoi\Command\Definition;

class Diff extends Command
{
    const ACTION_REARRANGE = 'rearrange';
    const ACTION_CHANGES = 'changes';
    const ACTION_REMOVALS = 'removals';
    const ACTION_ADDITIONS = 'additions';
    const ACTION_MODIFICATIONS = 'modifications';

    public $action;
    public $originalPath;
    public $newPath;
    public $out;
    public $showPaths;
    public $showJson;


    /**
     * @param Definition $definition
     * @param \stdClass|static $options
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $definition->name = 'json-diff';
        $definition->version = 'v1.1.0';
        $definition->description = 'JSON diff and rearrange tool for PHP, https://github.com/swaggest/json-diff';

        $options->action = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Action to perform')
            ->addToEnum(self::ACTION_REARRANGE)
            ->addToEnum(self::ACTION_CHANGES)
            ->addToEnum(self::ACTION_REMOVALS)
            ->addToEnum(self::ACTION_ADDITIONS)
            ->addToEnum(self::ACTION_MODIFICATIONS);

        $options->originalPath = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Path to old (original) json file');
        $options->newPath = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Path to new json file');
        $options->out = Command\Option::create()->setType()
            ->setDescription('Path to output result json file, STDOUT if not specified');
        $options->showPaths = Command\Option::create()->setDescription('Show JSON paths');
        $options->showJson = Command\Option::create()->setDescription('Show JSON result');
    }

    public function performAction()
    {
        $originalJson = file_get_contents($this->originalPath);
        if (!$originalJson) {
            $this->response->error('Unable to read ' . $this->originalPath);
            return;
        }

        $newJson = file_get_contents($this->newPath);
        if (!$newJson) {
            $this->response->error('Unable to read ' . $this->newPath);
            return;
        }

        $p = new JsonDiff(json_decode($originalJson), json_decode($newJson));

        $out = '';
        $paths = false;

        switch ($this->action) {
            case self::ACTION_REARRANGE:
                $out = $p->getRearranged();
                break;
            case self::ACTION_REMOVALS:
                $out = $p->getRemoved();
                $paths = $p->getRemovedPaths();
                break;
            case self::ACTION_ADDITIONS:
                $out = $p->getAdded();
                $paths = $p->getAddedPaths();
                break;
            case self::ACTION_MODIFICATIONS:
                $out = array('modifiedOriginal' => $p->getModifiedOriginal(), 'modifiedNew' => $p->getModifiedNew());
                $paths = $p->getModifiedPaths();
                break;
            case self::ACTION_CHANGES:
                $out = array(
                    'removals' => $p->getRemoved(),
                    'additions' => $p->getAdded(),
                    'modifiedOriginal' => $p->getModifiedOriginal(),
                    'modifiedNew' => $p->getModifiedNew()
                );
                $paths = array_merge($p->getRemovedPaths(), $p->getAddedPaths(), $p->getModifiedPaths());
                break;
        }

        if ($paths && $this->showPaths) {
            echo implode("\n", $paths), "\n";
        }

        $outJson = json_encode($out, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
        if ($this->out) {
            file_put_contents($this->out, $outJson);
        } else {
            if ($this->showJson) {
                echo $outJson, "\n";
            }
        }
    }
}