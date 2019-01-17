#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command(
    'hello1.php', 
    'picks the version from version file', 
    function($cmd){
        $cmd->writeln("version loaded from file: " . $cmd->getVersion(), "yellow");
    },
    null # the version file be checked, if present its contents will be used as version
);

$cmd->run();
