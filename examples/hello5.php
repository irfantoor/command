<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello',
    'description' => 'test the required argument',
    'handler' => function($cmd){
        $required = $cmd->getArgument('required');
        $cmd->writeln("OK the required argument is: " . $required, 'green');
    },
    'version' => '2.0',
]);

$cmd->addArgument('required', 'its a required argument', $cmd::ARGUMENT_REQUIRED);
$cmd->run();
