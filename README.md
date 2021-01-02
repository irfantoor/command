# IrfanTOOR\Command

Simplest way to make your console commands.

Note: Since the inclusion of irfantoor\terminal, your commands can be run through
a browser as well.

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
```

If the default value of an option is not provided its assumed as integer and
every of its presense as an option will add 1 to its current value, e.g:
command -v  # verbosity = 1
command -vv # verbosuty = 2

If the default value of an option is a string, whenever such option is
used it must always be followed by its value. For example hello4.php with a greeting "Hi" can be used in this way:

```sh
php hello4.php hello -g Hi irfan
# or
php hello4.php hello --greeting "Hay you! cheers" man
```

The arguments can be parsed as a string in url, or can be POSTed:
```sh
$ php hello4.php -h
# http://localhost:8000/hello4.php?args=-h


$ php hello4.php hello -g Hi irfan

# args as string:
# http://localhost:8000/hello4.php?args=hello%20-g%20Hi%20irfan

# args as array
# http://localhost:8000/hello4.php?args[]=hello&args[]=-g&args[]=Hi&args[]=irfan

# args must be used as array, when args values has spaces e.g.:
# http://localhost:8000/hello4.php?args[]=hello&args[]=-g&args[]=Hay%20You!&args[]=young%20man
```

ex:
```sh
$ php hello5.php        
|  Error: Missing argument: required
                                            
$ php hello5.php -h
hello 2.0

test the required argument

usage: hello [options] [--] <required>

options:
 -h, --help     Displays help
 -V, --version  Displays version
     --ansi     force ANSI outupt
     --no-ansi  disable ANSI output
 -v, --verbose  Adds verbosity

arguments:
 required    its a required argument [required]

$ php hello5.php Hello
OK the required argument is: Hello
```
