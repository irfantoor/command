<?php

use IrfanTOOR\{
    Command,
    Debug,
    Terminal,
    Test,
};
use Tests\MockCommand;

class CommandTest extends Test
{
    public function testCommandInstance()
    {
        $cmd = new Command();
        $this->assertInstanceOf(Command::class, $cmd);
        $this->assertInstanceOf(Terminal::class, $cmd);

        $cmd = new MockCommand();
        $this->assertInstanceOf(Command::class, $cmd);
    }

    public function testConstruct()
    {
        # no handler
        $cmd = new Command();
        $cmd->ob_start();
        $cmd->help();
        $help = $cmd->ob_get_clean();

        $cmd->ob_start();
        $cmd->main();
        $main = $cmd->ob_get_clean();
        $this->assertEquals($help, $main);

        $cmd->ob_start();
        $cmd->run();
        $run = $cmd->ob_get_clean();
        $this->assertEquals($help, $run);

        # without version
        $c = new MockCommand();
        $v = $c->getVersion();

        $this->assertNotEmpty($v);
        $this->assertString($v);
        $this->assertNotEquals('VERSION', $v);
        $this->assertNotInt(strpos($v, 'VERSION'));


        # with version
        $c = new MockCommand([
            'version' => '@@VERSION',
        ]);

        $v = $c->getVersion();

        # version
        $this->assertNotEmpty($v);
        $this->assertString($v);
        $this->assertNotEquals('VERSION', $v);
        $this->assertEquals('@@VERSION', $v);

        # throw exception
        $c = new MockCommand([
            'name' => '@@NAME',
            'description' => '@@DESCRIPTION'
        ]);

        $_SERVER['argv'] = ['cmd', '--go'];

        $this->assertException(function() use($c){
            $c->run();
        });

        // $level = Debug::getLevel();

        $options = [
            '-h', '--help',
            '-V', '--version',
            '--ansi',
            '--no-ansi',
            // '-v', '--verbose'
        ];

        foreach ($options as $option)
        {
            # must not throw an exception
            $c = new MockCommand();
            $c->ob_start();

            $_SERVER['argv'] = ['cmd', $option];

            $this->assertNotException(function() use($c){
                $c->run();
            });

            $c->ob_get_clean();
        }

        // Debug::enable($level);
    }

    public function testExecute()
    {
        $cmd = new MockCommand();
        $result = $cmd->execute('echo "Hello World!"');

        $this->assertZero($result['exit_code']);
        $this->assertEquals('Hello World!' . "\n", $result['output']);

        $result = $cmd->execute('itsNotAValidCommad');
        $this->assertEquals(127, $result['exit_code']);
        $this->assertNotEquals('', $result['output']);

        ob_start();
        System('date');
        $date = ob_get_clean();

        $result = $cmd->execute('date');
        $this->assertZero($result['exit_code']);
        $this->assertEquals($date, $result['output']);
    }


    function testCommandHelp(): void
    {
        $cmd = new MockCommand([
            'version' => '1.0'
        ]);

        ob_start();
        $cmd->help();
        $help = ob_get_clean();

        $this->assertNotEmpty($help);
        $this->assertString($help);
        $this->assertInt(strpos($help, '@@NAME'));
        $this->assertInt(strpos($help, '@@DESCRIPTION'));
        $this->assertInt(strpos($help, 'usage'));
        $this->assertInt(strpos($help, '@@NAME [options]'));
        $this->assertInt(strpos($help, '1.0'));
        $this->assertInt(strpos($help, 'options:'));
        $this->assertInt(strpos($help, '-h'));
        $this->assertInt(strpos($help, '--help'));
        $this->assertInt(strpos($help, 'Displays help'));
        $this->assertInt(strpos($help, '-v'));
        $this->assertInt(strpos($help, '--verbose'));
        $this->assertInt(strpos($help, 'Adds verbosity'));
        $this->assertInt(strpos($help, '-V'));
        $this->assertInt(strpos($help, '--version'));
        $this->assertInt(strpos($help, 'Displays version'));
    }

    public function testCommandRun(): void
    {
        $cmd = new MockCommand();

        ob_start();
        $cmd->help();
        $help = ob_get_clean();

        ob_start();
        $cmd->run();
        $run = ob_get_clean();

        $this->assertEquals($help, $run);
    }


    public function getVerbosityLevels()
    {
        return ['', 'v', 'vv', 'vvv'];
    }

    /**
     * l: $this->getVerbosityLevels()
     */
    public function testDebugVerbosityLevel($l)
    {
        $cmd = new MockCommand();

        ob_start();
        $cmd->run([
            'cmd',
            '-' . $l
        ]);
        ob_get_clean();

        $options = $cmd->get('options');
        $version = $options['verbose']['value'] ?? 0;

        $this->assertEquals($version, strlen($l));

        $files = get_included_files();
        $found = false;

        foreach ($files as $file) {
            if (strpos($file, 'vendor/irfantoor/debug/src/Debug.php') !== false) {
                $found = true;
                break;
            }
        }

        if ($version === 0) {
            # the file Debug is not included for debug level 0
            $this->assertFalse($found);
        } else {
            # the file Debug is included for debug level 1 (and onwards -- # todo)
            $this->assertTrue($found);
        }
    }

        # todo -- test init
        # todo -- test config
        # todo -- argument parsing tests...
}
