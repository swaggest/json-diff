<?php

namespace Swaggest\JsonDiff\Cli;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonPatch;
use Yaoi\Command;
use Yaoi\Command\Definition;

class Apply extends Base
{
    public $patchPath;
    public $basePath;
    public $isURIFragmentId = false;

    /**
     * @param Definition $definition
     * @param \stdClass|static $options
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->patchPath = Command\Option::create()->setType()->setIsUnnamed()
            ->setDescription('Path to JSON patch file');
        $options->basePath = Command\Option::create()->setType()->setIsUnnamed()
            ->setDescription('Path to JSON base file');
        $options->pretty = Command\Option::create()
            ->setDescription('Pretty-print result JSON');
        $options->rearrangeArrays = Command\Option::create()
            ->setDescription('Rearrange arrays to match original');
        $definition->description = 'Apply patch to base json document, output to STDOUT';

    }

    public function performAction()
    {
        $patchJson = file_get_contents($this->patchPath);
        if (!$patchJson) {
            $this->response->error('Unable to read ' . $this->patchPath);
            return;
        }

        $baseJson = file_get_contents($this->basePath);
        if (!$baseJson) {
            $this->response->error('Unable to read ' . $this->basePath);
            return;
        }

        try {
            $patch = JsonPatch::import(json_decode($patchJson));
            $base = json_decode($baseJson);
            $patch->apply($base);
            $this->out = $base;
        } catch (Exception $e) {
            $this->response->error($e->getMessage());
        }

        $this->postPerform();
    }

}