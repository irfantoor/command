#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

# from hello3.php
class HelloCommand extends Command
{
    function __construct()
    {
        parent::__construct(
            'hello', 
            'hello world! of command', 
            null,
            '1.3'
        );

        $this->addOption('g', 'greeting', 'Sets the greeting', self::ARGUMENT_OPTIONAL, 'Hello');
        $this->addOperand('name', 'Name to be greeted', self::ARGUMENT_REQUIRED);
    }

    function main()
    {
        $greeting = $this->getOption('greeting');
        $name = ucfirst($this->getOperand('name'));

        $this->writeln($greeting . " " . $name, "yellow");
    }
}

class CalCommand extends Command
{
    function __construct()
    {
        parent::__construct(
            'cal', 
            'prints a calendar', 
            null,
            '1.0'
        );
    }

    function main()
    {
        $result = $this->system('cal');
        if ($result['exit_code'] === 0)
            $this->write($result['output'], 'cyan');
        else
            $this->write($result['output'], 'red');
    }
}

$cmd = new Command(
    'hello4.php',
    'Its a composite command, i.e. it contains commmands',
    function($cmd) {
        $cmd->help();
    },
    '1.4'
);

$cmd->addCommand(new HelloCommand);
$cmd->addCommand(new CalCommand);
$cmd->run();
