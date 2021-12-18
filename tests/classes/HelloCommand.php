<?php

namespace Tests;

use IrfanTOOR\Command;

class HelloCommand extends Command
{
    protected $handler;

    function __construct($init = [])
    {
        parent::__construct();

        $this->setName($init['name'] ?? 'hello');
        $this->setDescription($init['description'] ?? 'Hello Command');

        $this->addCommand('world', Tests\WorldCommand::class);
    }

    function main()
    {
        echo "Hello";
    }
}
