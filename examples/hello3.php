<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

class HelloCommand extends Command
{
    function __construct()
    {
        parent::__construct([
            'name' => 'hello', 
            'description' => 'hello world! of command', 
            'version' => '1.3'
        ]);
    }

    function init()
    {
        $this->addOption('g|greeting', 'Sets the greeting', 'Hello');
        $this->addArgument('name', 'Name to be greeted', self::ARGUMENT_OPTIONAL, 'World');        
    }

    function main()
    {
        $greeting = $this->getOption('greeting');
        $name = ucfirst($this->getArgument('name'));

        $this->writeln($greeting . " " . $name, "yellow");
    }
}

$cmd = new HelloCommand();
$cmd->run();
