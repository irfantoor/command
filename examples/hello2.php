#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;


// $cmd = new Command('test', 'Its a test command');

$cmd = new Command([
    'name' => 'hello2.php', 
    'description' => 'hello world! of command', 
    'handler' => function($cmd){
        $greeting = $cmd->getOption('greeting');
        $name = ucfirst($cmd->getArgument('name'));

        $cmd->writeln($greeting . " " . $name, "yellow");
    }, 
    'version' => '1.2'
]);

# -g=Hi or --greeting="Hi there,"
$cmd->addOption('g', 'greeting', 'Sets the greeting', $cmd::ARGUMENT_OPTIONAL, 'Hello');
$cmd->addArgument('name', 'Name to be greeted', $cmd::ARGUMENT_OPTIONAL, 'World');

$cmd->run();
