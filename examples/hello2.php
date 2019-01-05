#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;


// $cmd = new Command('test', 'Its a test command');

$cmd = new Command(
    'hello2.php', 
    'hello world! of command', 
    function($cmd){
        $greeting = $cmd->getOption('greeting');
        $name = ucfirst($cmd->getOperand('name'));

        $cmd->writeln($greeting . " " . $name, "yellow");
    }, 
    '1.2'
);

# -g=Hi or --greeting="Hi there,"
$cmd->addOption('g', 'greeting', 'Sets the greeting', $cmd::ARGUMENT_OPTIONAL, 'Hello');
$cmd->addOperand('name', 'Name to be greeted', $cmd::ARGUMENT_OPTIONAL, 'World');

$cmd->run();
