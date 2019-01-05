#!/usr/bin/env php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command(
    'hello5',
    'test the required option',
    function($cmd){
        $req = $cmd->getOption('required');

        $cmd->writeln("OK the required option is: " . $req, 'green');
    },
    '2.0',
    true    # throws exceptions
);

$cmd->addOption('r', 'required', 'its an option which is required', $cmd::ARGUMENT_REQUIRED);
$cmd->run();
