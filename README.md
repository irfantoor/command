# IrfanTOOR\Command

Simplest way to make your console commands.

Examples:

A simple command to say "Hello World!"" on command line. The default options of verbosity, version and help are defained by default. So you can do like:

```sh
$ php hello1.php --help
$ php hello1.php -V
$ php hello1.php -v
```

ref: examples/hello1.php
```php
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
```

A more complex example of a composite commmand is as follows:

Here the option greeting is optional, and the operand name is required.

ex:
```sh
$ php hello4.php hello world!
$ php hello4.php hello -g=Hi irfan
$ php hello4.php hello --greeting="Hi there," buddy
```

ref: examples/hello4.php
```php
<?php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

# from hello3.php
class HelloCommand extends Command
{
    function __construct()
    {
        parent::__construct(
            'hello', 
            'hello world! of command', 
            null,
            '1.3'
        );

        $this->addOption('g', 'greeting', 'Sets the greeting', self::ARGUMENT_OPTIONAL, 'Hello');
        $this->addOperand('name', 'Name to be greeted', self::ARGUMENT_REQUIRED);
    }

    function main()
    {
        $greeting = $this->getOption('greeting');
        $name = ucfirst($this->getOperand('name'));

        $this->writeln($greeting . " " . $name, "yellow");
    }
}

class CalCommand extends Command
{
    function __construct()
    {
        parent::__construct(
            'cal', 
            'prints a calendar', 
            null,
            '1.0'
        );
    }

    function main()
    {
        $result = $this->system('cal');
        if ($result['exit_code'] === 0)
            $this->write($result['output'], 'cyan');
        else
            $this->write($result['output'], 'red');
    }
}

$cmd = new Command(
    'hello4.php',
    'Its a composite command, i.e. it contains commmands',
    function($cmd) {
        $cmd->help();
    },
    '1.4'
);

$cmd->addCommand(new HelloCommand);
$cmd->addCommand(new CalCommand);
$cmd->run();
```

Note that optional value is provided after an '=' as in previous example, but if an option is required it can proceed without an '=' sign.

ex:
```sh
$ php hello5.php
                                            
  Error - Missing option value: --required  
                                            
$ php hello5.php -h
hello5 2.0
test the required option

usage: hello5 [options]

Options:
 -h, --help         Displays this help and quit
 -r, --required     its an option which is required [required]
 -v, --verbose      Adds verbosity
 -V, --version      Displays version and quit


$ php hello5.php -rHello
OK the required option is: Hello

$ php hello5.php --required World!
OK the required option is: World!
```

ref: examples/hello5.php
```php
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
    '2.0'
);

$cmd->addOption('r', 'required', 'its an option which is required', $cmd::ARGUMENT_REQUIRED);
$cmd->run();
```
