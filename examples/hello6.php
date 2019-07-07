#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello1.php', 
    'description' => 'picks the version from version file', 
    'handler' => function($cmd){
        $cmd->writeln("showing default version: " . $cmd->getVersion(), "yellow");
    },
]);

$cmd->run();
