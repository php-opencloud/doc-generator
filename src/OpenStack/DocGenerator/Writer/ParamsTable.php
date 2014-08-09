<?php

namespace OpenStack\DocGenerator\Writer;

use Pandoc\Pandoc;

class ParamsTable extends AbstractWriter
{
    public function write()
    {
        $this->writeSectionHeader('Additional Parameters');
        $this->writeTitles();

        $this->writeParamTable();

        $this->flushBuffer();
    }

    private function writeTitles()
    {
        return 'Name|Type|Required|Description' . PHP_EOL . '---|---|---|---' . PHP_EOL;
    }

    private function writeParamTable()
    {
        $content = $this->writeTitles();

        foreach ($this->method->getParameters() as $param) {
            if ($param->getName() == 'options') {
                $proceed = true;
            }
        }

        if (!isset($proceed)) {
            return;
        }

        // @TODO create bespoke DocBlock class or something
        $docBlock = $this->getParsedDocBlock();
        foreach ($docBlock->getTag('param') as $param) {
            if ($param[0][0][0] == '$options') {
                $operationName = str_replace(['{', '}'], '', $param[2]);
            }
        }

        if (!isset($operationName)
            || !($operation = $this->description->getOperation($operationName))
        ) {
            return;
        }

        foreach ($operation->getParams() as $param) {
            if ($param->getStatic()) {
                continue;
            }

            $name = $param->getName();
            $type = $param->getType();

            if (is_array($type)) {
                $type = implode('|', $type);
            }

            if ($enum = $param->getEnum()) {
                array_walk($enum, function(&$val) {
                    $val = "'{$val}'";
                });
                $type = implode(",", $enum);
            }

            $content .= sprintf(
                '%s|%s|%s|%s',
                $name,
                $type,
                $param->getRequired() ? 'Yes' : 'No',
                wordwrap($param->getDescription(), 50, '\\'.PHP_EOL)
            ) . PHP_EOL;
        }

        $rstContent = (new Pandoc())->convert($content, 'markdown', 'rst');

        $this->buffer(trim($rstContent), false, false);
    }
}