<?php

namespace Tests;

use IrfanTOOR\Command;

class MockCommand extends Command
{
    protected $handler;

    function __construct($init = [])
    {
        parent::__construct();

        $this->setName($init['name'] ?? '@@NAME');
        $this->setDescription($init['description'] ?? '@@DESCRIPTION');

        if (isset($init['version'])) {
            $this->setVersion($init['version']);
        }

        $this->handler = $init['handler'] ?? null;
    }

    public function get($var)
    {
        return $this->command[$var] ?? null;
    }
}
