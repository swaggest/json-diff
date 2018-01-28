<?php

namespace Swaggest\JsonDiff\Cli;


use Swaggest\JsonDiff\JsonDiff;
use Yaoi\Command;

abstract class Base extends Command
{
    public $originalPath;
    public $newPath;
    public $pretty;
    public $rearrangeArrays;

    static function setUpDefinition(Command\Definition $definition, $options)
    {
        $options->originalPath = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Path to old (original) json file');
        $options->newPath = Command\Option::create()->setIsUnnamed()->setIsRequired()
            ->setDescription('Path to new json file');
        $options->pretty = Command\Option::create()
            ->setDescription('Pretty-print result JSON');
        $options->rearrangeArrays = Command\Option::create()
            ->setDescription('Rearrange arrays to match original');
    }


    /** @var JsonDiff */
    protected $diff;
    protected $out;

    protected function prePerform()
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

        $options = 0;
        if ($this->rearrangeArrays) {
            $options += JsonDiff::REARRANGE_ARRAYS;
        }
        $this->diff = new JsonDiff(json_decode($originalJson), json_decode($newJson), $options);

        $this->out = '';
    }

    protected function postPerform()
    {
        $options = JSON_UNESCAPED_SLASHES;
        if ($this->pretty) {
            $options += JSON_PRETTY_PRINT;
        }

        $outJson = json_encode($this->out, $options);
        $this->response->addContent($outJson);
    }
}