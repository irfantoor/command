<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello', 
    'description' => 'hello world! of command', 
    'handler' => function($cmd){
        $cmd->writeln("Hello World!", "yellow");
    },
    'version' => '1.1'
]);

$cmd->run();
