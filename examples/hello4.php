<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;
use IrfanTOOR\Debug;
Debug::enable(1);

class HelloCommand extends Command
{
    function __construct()
    {
        parent::__construct([
            'name' => 'hello', 
            'description' => 'hello world! of command',
            'version' => '1.3',
        ]);
    }

    function init()
    {
        $this->addOption('g|greeting', 'Sets the greeting', 'Hello');
        $this->addArgument('name', 'Name to be greeted', self::ARGUMENT_OPTIONAL, 'World!');
    }

    function main()
    {
        $greeting = $this->getOption('greeting');
        $name = ucfirst($this->getArgument('name'));

        $this->writeln($greeting . " " . $name, "yellow");
    }
}

class CalCommand extends Command
{
    function __construct()
    {
        parent::__construct([
            'name' => 'cal', 
            'description' => 'prints a calendar', 
            'version' => '0.1'
        ]);
    }

    function main()
    {
        ob_start();
        system("cal");
        $output = ob_get_clean();
        $this->writeln($output, "yellow");
    }
}

$cmd = new Command([
    'name' => 'hello4',
    'description' => "Composit command",
    'version' => '1.4',
    'handler' => function($cmd) {
    }
]);

$cmd->addCommand(HelloCommand::class);
$cmd->addCommand(CalCommand::class);

$cmd->run();
