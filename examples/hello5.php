#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello5',
    'description' => 'test the required option',
    'handler' => function($cmd){
        $req = $cmd->getOption('required');
        $cmd->writeln("OK the required option is: " . $req, 'green');
    },
    'version' => '2.0',
]);

$cmd->addOption('r', 'required', 'its an option which is required', $cmd::ARGUMENT_REQUIRED);
$cmd->run();
