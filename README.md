# IrfanTOOR\Command

Simplest way to make your console commands.

Examples:

A simple command to say "Hello World!"" on command line. The default options of verbosity, version and help are defained by default. So you can do like:

```sh
$ php hello1.php --help
$ php hello1.php -V
$ php hello1.php -v
```

Note: If version is not provided while intialising, file named 'version' is searched in the parent directory where the /src directory is present, if found then its contents are used as version, or a default value of '0.1' is used. This is helpful if the version file is part of versioning system and is updated to the last version tag (ref: examples/hello6.php)

ref: examples/hello1.php
```php
<?php
require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Command;

$cmd = new Command([
    'name' => 'hello1.php', 
    'description' => 'hello world! of command', 
    'handler' => function($cmd){
        $cmd->writeln("Hello World!", "yellow");
    },
    'version' => '1.1'
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

class HelloCommand extends Command
{
    function __construct()
    {
        parent::__construct([
            'name' => 'hello', 
            'description' => 'hello world! of command', 
            'version' => '1.3'
        ]);

        $this->addOption('g', 'greeting', 'Sets the greeting', self::ARGUMENT_OPTIONAL, 'Hello');
        $this->addArgument('name', 'Name to be greeted', self::ARGUMENT_REQUIRED);
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
            'version' => '1.0'
        ]);
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

$cmd = new Command([
    'name' => 'hello4.php',
    'description' => 'Its a composite command, i.e. it contains commmands',
    'handler' => function($cmd) {
        $cmd->help();
    },
    'version' => '1.4'
]);

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

options:
 -h, --help      Displays help
 -V, --version   Displays version
 -v, --verbose   Adds verbosity
 -r, --required  its an option which is required [required]

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

$cmd = new Command([
    'name' => 'hello5',
    'description' => 'test the required option',
    'handler' => function($cmd){
        $req = $cmd->getOption('required');
        $cmd->writeln("OK the required option is: " . $req, 'green');
    },
    'version' => '2.0',
]);

$cmd->addOption('r', 'required', 'its an option which is required', $cmd::ARGUMENT_REQUIRED);
$cmd->run();
```
