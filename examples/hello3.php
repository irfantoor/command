#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

class HelloCommand extends Command
{
    function __construct()
    {
        parent::__construct(
            'hello3.php', 
            'hello world! of command', 
            null,
            '1.3'
        );

        $this->addOption('g', 'greeting', 'Sets the greeting', self::ARGUMENT_OPTIONAL, 'Hello');
        $this->addOperand('name', 'Name to be greeted', self::ARGUMENT_OPTIONAL, 'World');
    }

    function main()
    {
        $greeting = $this->getOption('greeting');
        $name = ucfirst($this->getOperand('name'));

        $this->writeln($greeting . " " . $name, "yellow");
    }
}

$cmd = new HelloCommand();
$cmd->run();
