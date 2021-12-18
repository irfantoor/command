<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command(
    [
        'name' => 'hello1.1', 
        'description' => 'hello world! of command', 
        'handler' => function($cmd){
            $name = $cmd->read('your name: ', 'green');
            $cmd->writeln("Hello " . ucwords($name) . '!', "yellow");
        },
        'version' => '1.1'
    ]
);

$cmd->run();
