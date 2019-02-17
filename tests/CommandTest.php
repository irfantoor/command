<?php

use IrfanTOOR\Console;
use IrfanTOOR\Command;
use IrfanTOOR\Test;

class MockCommand extends Command
{
    function __construct()
    {
        parent::__construct('@@NAME', '@@DESCRIPTION');
    }

    function main()
    {
        echo '@@MAIN';
    }
}

class CommandTest extends Test
{
    function testCommandInstance()
    {
        $c = new Command('test', 'its a test');
        $this->assertInstanceOf(IrfanTOOR\Command::class, $c);
    }

    function testConstruct()
    {
        # no handler
        $c = new Command('@@NAME', '@@DESCRIPTION');
        ob_start();
        $c->help();
        $help = ob_get_clean();

        ob_start();
        $c->run();
        $main = ob_get_clean();

        $this->assertEquals($help, $main);

        # handler
        $c = new Command('@@NAME', '@@DESCRIPTION', function(){
            echo 'Hello World!';
        });

        ob_start();
        $c->run();
        $main = ob_get_clean();

        $this->assertEquals('Hello World!', $main);

        # without version
        $c = new Command('@@NAME', '@@DESCRIPTION');
        $v = $c->getVersion();

        $this->assertNotEmpty($v);
        $this->assertString($v);
        $this->assertNotEquals('VERSION', $v);
        $this->assertNotInt(strpos($v, 'VERSION'));

        # with version
        $c = new Command('@@NAME', '@@DESCRIPTION', null, '@@VERSION');
        $v = $c->getVersion();

        # version
        $this->assertNotEmpty($v);
        $this->assertString($v);
        $this->assertNotEquals('VERSION', $v);
        $this->assertEquals('@@VERSION', $v);

        # throw exception
        $c = new Command('@@NAME', '@@DESCRIPTION', null, null, true);
        $this->assertException(function() use($c){
            $c->run(['--go']);
        });

        # throw no exception
        $c = new Command('@@NAME', '@@DESCRIPTION', null, null, false);
        $this->assertException(function() use($c){
            $c->run(['--go']);
        });
    }

    function test__Call()
    {
        $c = new Console;
        $cmd = new Command('@@NAME', '@@DESCRIPTION');

        ob_start();
        $c->writeln('Hello World!', ['green']);
        $out1 = ob_get_clean();

        ob_start();
        $cmd->writeln('Hello World!', ['green']);
        $out2 = ob_get_clean();

        $this->assertString($out1);
        $this->assertNotEquals('Hello World!', $out1);
        $this->assertEquals($out1, $out2);
    }

    function testCommandHelp(): void
    {
        $cmd = new Command('@@NAME', '@@DESCRIPTION',null, '1.0');
        # $help = $cmd->optHelp([], []);
        ob_start();
        $cmd->help();
        $help = ob_get_clean();

        $this->assertNotEmpty($help);
        $this->assertString($help);
        $this->assertInt(strpos($help, '@@NAME'));
        $this->assertInt(strpos($help, '@@DESCRIPTION'));
        $this->assertInt(strpos($help, 'usage: @@NAME [options]'));
        $this->assertInt(strpos($help, '1.0'));
        $this->assertInt(strpos($help, 'Options:'));
        $this->assertInt(strpos($help, '-h'));
        $this->assertInt(strpos($help, '--help'));
        $this->assertInt(strpos($help, 'Displays this help and quit'));
        $this->assertInt(strpos($help, '-v'));
        $this->assertInt(strpos($help, '--verbose'));
        $this->assertInt(strpos($help, 'Adds verbosity'));
        $this->assertInt(strpos($help, '-V'));
        $this->assertInt(strpos($help, '--version'));
        $this->assertInt(strpos($help, 'Displays version and quit'));
    }

    public function testCommandRun(): void
    {
        $cmd = new Command('@@NAME', '@@DESCRIPTION');
        ob_start();
        $cmd->help();
        $help_output = ob_get_clean();

        ob_start();
        $cmd->run();
        $run_output = ob_get_clean();

        $this->assertEquals($help_output, $run_output);

        $cmd = new MockCommand();
        ob_start();
        $cmd->run();
        $run_output = ob_get_clean();

        $this->assertEquals('@@MAIN', $run_output);
    }

    function testSystem(): void
    {
        $cmd = new MockCommand();
        $result = $cmd->system('echo "Hello World!"');

        $this->assertZero($result['exit_code']);
        $this->assertEquals('Hello World!' . "\n", $result['output']);

        $result = $cmd->system('itsNotAValidCommad');
        $this->assertEquals(127, $result['exit_code']);

        ob_start();
        System('date');
        $date = ob_get_clean();

        $result = $cmd->system('date');
        $this->assertZero($result['exit_code']);
        $this->assertEquals($date, $result['output']);
    }
}
