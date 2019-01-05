#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command(
    'hello1.php', 
    'hello world! of command', 
    function($cmd){
        $cmd->writeln("Hello World!", "yellow");
    },
    '1.1'
);

$cmd->run();
