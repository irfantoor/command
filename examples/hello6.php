<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello1.php', 
    'description' => 'picks the version from the const: VERSION', 
    'handler' => function($cmd){
        $cmd->writeln("version is: " . $cmd->getVersion(), "yellow");
    },
]);

$cmd->run();
