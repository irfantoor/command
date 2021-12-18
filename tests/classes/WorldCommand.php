<?php

namespace Tests;

use IrfanTOOR\Command;

class WorldCommand extends Command
{
    protected $handler;

    function __construct($init = [])
    {
        parent::__construct();

        $this->setName($init['name'] ?? 'world');
        $this->setDescription($init['description'] ?? 'World Command');
    }

    function main()
    {
        echo "World!";
    }
}
