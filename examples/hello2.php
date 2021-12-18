<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello', 
    'description' => 'hello world! of command', 
    'handler' => function($cmd){
        $greeting = $cmd->getOption('greeting');
        $name = ucfirst($cmd->getArgument('name'));

        $cmd->writeln($greeting . " " . $name, "yellow");
    }, 
    'version' => '1.2'
]);

# -g=Hi or --greeting="Hi there,"
$cmd->addOption('g|greeting', 'Sets the greeting', 'Hello');
$cmd->addArgument('name', 'Name to be greeted', $cmd::ARGUMENT_OPTIONAL, 'World');

$cmd->run();
