<?php

/**
 * IrfanTOOR\Command
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\{
    Terminal,
    Debug
};
use Throwable;

/**
 * Create shell commands, using php
 */
class Command extends Terminal
{
    const NAME        = "Command";
    const DESCRIPTION = "Create shell commands, using php";
    const VERSION     = "0.6";

    # Return codes
    const SUCCESS = 0;
    const FAILED  = 1;

    # Argument types
    const ARGUMENT_OPTIONAL = 1;
    const ARGUMENT_REQUIRED = 2;

    # Command definition
    protected $command = [
        # name, version & description
        "name"        => "",
        "version"     => "",
        "description" => "",

        # Usage and help
        "usage"       => [],
        "help"        => null,

        # Optional parameters
        "options"     => [],
        "arguments"   => [],
        "commands"    => [],

        # The command to run, if any
        "command"      => null,
        "command_args" => [],

        # handler
        "handler"     => null,
    ];

    /**
     * Command constructor
     *
     * @param array
     */
    public function __construct(array $init = [])
    {
        parent::__construct();
        error_reporting(0);

        # initialize
        $this->setName($init['name'] ?? self::NAME);
        $this->setDescription($init['description'] ?? self::DESCRIPTION);
        $this->setVersion($init['version'] ?? self::VERSION);
        $this->setHandler($init['handler'] ?? null);

        # Default options, present in all commands
        $this->addOption('h|help',    'Displays help');
        $this->addOption('V|version', 'Displays version');

        $this->addOption('ansi',      'force ANSI outupt');
        $this->addOption('no-ansi',   'disable ANSI output');

        $this->addOption('v|verbose', 'Adds verbosity');
    }

    /**
     * Sets the command name
     *
     * @param string $name Name of the command
     */
    #
    # @param string $name
    public function setName(string $name)
    {
        $this->command['name'] = $name;
    }

    /**
     * Sets the command version
     *
     * @param string $version
     */
    public function setVersion(string $version)
    {
        $this->command['version'] = $version;
    }

    /**
     * Sets the command description
     *
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->command['description'] = $description;
    }

    /**
     * Sets the command handler function
     *
     * @param closure|invokable $handler
     */
    public function setHandler($handler)
    {
        $this->command['handler'] = $handler;
    }

    /**
     * Initializes the command, with options and arguments
     * Note: Add options and arguments here in your derived calss
     */
    protected function init()
    {
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
    }

    /**
     * Adds an option
     * Note: single letter is preceded with one '-' and multi-letter with two
     *       '--', while passing an option e.g -h -a -s or --simple --help etc.
     *       When a combination of letters is preceeded with a '-' all of the
     *       letters are considered as separate options:
     *          e.g. -has is like: -h -a -s
     *
     * @param string $name        e.g 'h|help' or 'capital' (without short name)
     * @param string $description e.g. "Displays help"
     * @param mixed  $default     Default value of the option, if this is present
     *                            the must be provided, when using this option
     */
    public function addOption(string $name, string $description, $default = null)
    {
        $list = explode('|', $name);
        $short = null;

        foreach ($list as $v) {
            $v = trim($v);

            if (strlen($v) === 1) {
                $short = $v;
            } else {
                $long = $v;
            }
        }

        $this->command['options'][$long] = [
            'short'       => $short,
            'long'        => $long,
            'description' => $description,

            # this will be changed while passed parameter parsing
            'value'       => $default,
        ];
    }

    /**
     * Adds a command argument
     *
     * @param string $name Name of the argument e.g. "path"
     * @param array  $def  ['description' => ..., 'type' => ...]
     */
    public function addArgument(
        string $name,
        string $description,
        int $type = self::ARGUMENT_OPTIONAL,
        $default = null
        )
    {
        $this->command['arguments'][$name] = [
            'name'        => $name,
            'description' => $description,
            'type'        => $type,
            'default'     => $default,

            # this will be changed while passed parameter parsing
            'value'       => null,
        ];
    }

    /**
     * Adds a sub command to this command
     * Note: if a sub command is present
     *
     * @param string $name
     * @param string $class
     */
    public function addCommand(string $name, string $class)
    {
        $this->command['commands'][$name] = $class;
    }

    /**
     * Sets the value of an option programatically
     * Note: you might need to do an init() or configure()
     *
     * @param string          $name  Option name
     * @param null|int|string $value Option value
     */
    public function setOption(string $name, $value)
    {
        $this->command['options'][$name]['value'] = $value;
    }

    /**
     * Retrieve the value of an option
     *
     * @param string $name Name of the Option
     * @return null|int|string Value of the option, after parsing
     */
    public function getOption(string $name)
    {
        return $this->command['options'][$name]['value'] ?? null;
    }

    /**
     * Sets the value of an argument programatically
     * Note: you might need to do an init() or configure()
     *
     * @param string      $name  Name of the argument
     * @param null|string $value Value of the argument
     */
    public function setArgument(string $name, $value)
    {
        $this->command['arguments'][$name]['value'] = $value;
    }

    /** Retrieve the value of an argument
     *
     * @param string $name Argument name
     * @return null|string Argument value (after parsing)
     */
    public function getArgument(string $name)
    {
        return $this->command['arguments'][$name]['value'] ?? $this->command['arguments'][$name]['default'];
    }

    public function getCommand(string $cmd)
    {
        return $this->command['commands'][$cmd] ?? null;
    }

    /**
     * Retrieve the name of this command
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->command['name'];
    }

    public function normalizeVersion($version)
    {
        $v = explode('-', (string) $version);

        switch(count(explode('.', $v[0]))) {
            case 1:
                $v[0] .= '.0';
            case 2:
                $v[0] .= '.0';
            default:
        }

        return $v[0] . (isset($v[1]) ? '-' . $v[1] : '');
    }

    /**
     * Retrieve the command version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->normalizeVersion($this->command['version']);
    }

    /**
     * Retrive the command description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->command['description'];
    }

    /**
     * Prints the name of the command and its version
     */
    public function title()
    {
        $this->writeln($this->getName() . ' ' . $this->getVersion(), "info");
    }

    /**
     * Prints the help, called when an option -h or --help is provided
     */
    public function help()
    {
        $this->title();
        $this->writeln();
        $this->writeln($this->getDescription());
        $this->writeln();

        $this->write("usage: ", "info");

        if (!$this->command['help']) {
            $usage = $this->getName();

            if (count($this->command['commands']))
                $usage .= ' [<command>]';

            $usage .= ' [options]';

            if (count($this->command['arguments'])) {
                $usage .= ' [--]';

                foreach($this->command['arguments'] as $k => $v) {
                    if ($v['type'] == self::ARGUMENT_REQUIRED) {
                        $usage .= ' <' . $v['name'] . '>';
                    } else {
                        $usage .= ' [<' . $k . '>]';
                    }
                }
            }

            if (PHP_SAPI != "cli") {
                $usage = htmlentities($usage);
            }

            $this->writeln($usage);
        } else {
            $sep = "";

            foreach ($this->command['usage'] as $usage) {
                $this->writeln($sep . $usage);
                $sep = "       ";
            }
        }

        # Options
        $this->writeln();

        if ($this->command['options']) {
            // ksort($this->command['options']);
            $this->writeln("options:", 'info');
            $max = 0;

            foreach ($this->command['options'] as $k => $v) {
                extract($v);
                $l = max(strlen($short), 6) + strlen($long) + 2;
                $max = max($max, $l);
            }

            foreach ($this->command['options'] as $k => $v) {
                extract($v);

                $s1 = strlen($short) ?: 4;
                $s2 = $max - strlen($long) - 6;

                $sep1 = str_repeat(' ', $s1);
                $sep2 = str_repeat(' ', $s2);

                $this->write(
                    $sep1 . ($short ? '-' . $short : '') . # -s
                    ($short && $long ? ',' : '') .         # ,
                    ($long ? ' --' . $long : '') .         # --long
                    $sep2,                                 # space before description
                    'green');                              # color

                $this->writeln($description);
            }

            $this->writeln();
        }

        # arguments
        if (count($this->command['arguments'])) {
            $this->writeln('arguments:', "info");

            $max = 0;
            foreach ($this->command['arguments'] as $k => $v) {
                $max = max($max, strlen($k));
            }

            $max += 4;

            foreach ($this->command['arguments'] as $argument) {
                extract($argument);

                $s = $max - strlen($name);
                $sep = str_repeat(' ', $s);

                $this->write(' ' . $name . $sep, 'green');
                $this->write($description);

                # print if the argument is required or optional and its default value if so
                if ($type == self::ARGUMENT_REQUIRED) {
                    $this->write(' [required]', 'dark');
                } elseif ($type == self::ARGUMENT_OPTIONAL) {
                    $this->write(' [optional, default: ' . print_r($default, 1) . ']', 'dark');
                }

                $this->writeln();
            }

            $this->writeln();
        }

        if (count($this->command['commands'])) {
            $this->writeln("commands:", 'info');
            $max = 0;

            foreach ($this->command['commands'] as $k => $v) {
                $max = max($max, strlen($k));
            }

            foreach ($this->command['commands'] as $k => $v) {
                $this->write("  " . $k . str_repeat(' ', $max + 4 - strlen($k)), 'green');
                $v = new $v();
                $this->writeln($v->getDescription());
            }

            $this->writeln();
        } else {
            $argument = "";
        }

        return self::SUCCESS;
    }

    /**
     * Prints the version information
     */
    public function version()
    {
        $this->title();
        return self::SUCCESS;
    }

    /**
     * Executes a command in the current path
     * todo -- if we cd to a path, it must keep track during the same call
     *
     * @param string $cmd System command to be executed
     * @return array Result is like: ["output" => "...", "exit_code" => 0]
     */
    public function execute(string $cmd, $show_output = false): array
    {
        $output    = "";
        $exit_code = 0;

        if ($cmd !== "") {
            if ($show_output) {
                system($cmd, $exit_code);
            } else {
                ob_start();
                system($cmd . " 2>/dev/stdout", $exit_code);
                $output = ob_get_clean();
            }
        }

        return compact(['output', 'exit_code']);
    }

    /**
     * Parses the passed arguments
     *
     * @param array $args
     */
    public function parseArguments(array $args)
    {
        # strip the command name
        array_shift($args);

        # what is expected?
        $command  = count($this->command['commands']) > 0;
        $options  = !$command;
        $arguments = false;
        $stop     = false;
        $waiting  = false;
        $option   = '';

        # process the passed args
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            if ($waiting) {
                $token = 'value';
            } elseif ($arg === '--') {
                $token = '--';
            } elseif (strpos($arg, '--') === 0) {
                $token = 'long';
                $arg = substr($arg, 2);
            } elseif (strpos($arg, '-') === 0) {
                $token = 'short';
                $arg = substr($arg, 1);
            } elseif ($command) {
                $token = 'command';
            } elseif ($arguments) {
                $token = 'argument';
            } else {
                $token = 'arg';
            }

            switch ($token) {
                case 'command':
                    if (array_key_exists($arg, $this->command['commands'])) {
                        $this->command['command'] = new $this->command['commands'][$arg];
                        $this->command['command_args'] = $args;
                    } else {
                        $list = array_keys($this->command['commands']);
                        $matched = [];
                        foreach ($list as $cmd) {
                            if (strpos($cmd, $arg) === 0)
                                $matched[] = $cmd;
                        }
                        if (count($matched) === 1) {
                            $cmd = $matched[0];
                            $this->command['command'] = $this->command['commands'][$cmd];
                            $this->command['command_args'] = $args;
                        } else {
                            throw new Exception("Unknown command: " . $arg, 1);
                        }
                    }
                    $stop = true;
                    break;

                case 'argument':
                case 'arg':
                    foreach ($this->command['arguments'] as $k => $v) {
                        $this->command['arguments'][$k]['value'] = $arg;
                        $i++;
                        if (!isset($args[$i]))
                            break;
                        else
                            $arg = $args[$i];
                    }

                    $stop = true;
                    break;

                case '--':
                    $command  = false;
                    $options  = false;
                    $arguments = true;
                    break;

                case 'short':
                    for ($l = 0; $l < strlen($arg); $l++) {
                        $a = $arg[$l];
                        $found = false;

                        foreach ($this->command['options'] as $k => $v) {
                            extract($v);
                            if ($short === $a) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            throw new Exception("Unknown option: " . $arg, 1);
                        }

                        if (is_null($this->command['options'][$k]['value'])) {
                            $this->command['options'][$k]['value'] = 1;
                        } elseif(is_int($this->command['options'][$k]['value'])) {
                            $this->command['options'][$k]['value'] += 1;
                        } else {
                            $waiting = true;
                            $option = $k;
                            break;
                        }
                    }

                    break;

                case 'long':
                    preg_match('|(\w*)=(.*)|s', $arg, $m);
                    $arg_value = null;

                    if (isset($m[1])) {
                        $arg = $m[1];
                        $arg_value = $m[2];
                    }

                    $found = false;

                    foreach ($this->command['options'] as $k => $v) {
                        extract($v);

                        if ($long === $arg) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        throw new Exception("Unknown option: " . $arg, 1);
                    }

                    if ($arg_value != null) {
                        $this->command['options'][$k]['value'] = $arg_value;
                    } else {
                        if (is_null($this->command['options'][$k]['value'])) {
                            $this->command['options'][$k]['value'] = 1;
                        } elseif(is_int($this->command['options'][$k]['value'])) {
                            $this->command['options'][$k]['value'] += 1;
                        } else {
                            $waiting = true;
                            $option = $k;
                            break;
                        }
                    }

                    break;

                case 'value':
                    $this->command['options'][$option]['value'] = $arg;
                    $waiting = false;
                    $option = "";
                    break;
            }

            if ($stop)
                break;
        }
    }

    /**
     * Run the command
     *
     * @param null|array $args Arguments are retrieved from env, if not provided
     */
    public function run(?array $args = null)
    {
        if (method_exists(parent::class, 'init'))
            parent::init();

        $this->init();

        # parse the passed options/arguments to run or the arguments passed
        # to main application
        if (!$args) {
            if (PHP_SAPI === "cli") {
                $args = $_SERVER['argv'];
            } else {
                $args = $_POST['args'] ?? $_GET['args'] ?? [];
                if (is_string($args)) {
                    $args = explode(' ', $args);
                }

                array_unshift($args, $this->command['name']);
            }
        }

        $this->parseArguments($args);

        # verbosity
        $dl = $this->getOption('verbose') ?? 1;

        if ($dl)
            Debug::enable($dl);

        if ($this->getOption('ansi'))
            $this->ansi(true);

        elseif ($this->getOption('no-ansi'))
            $this->ansi(false);

        $result = null;

        # help
        if ($option = $this->getOption('help')) {
            return $this->help($option);
        } elseif ($this->getOption('version')) {
            return $this->version();
        } elseif (count($this->command['commands'])) {
            if (!$this->command['command'])
                return $this->main();

            $cmd = new $this->command['command'];
            return $cmd->run($this->command['command_args']);
        } elseif (count($this->command['arguments'])) {
            # check if all required arguments have been provided
            foreach ($this->command['arguments'] as $k => $arg) {
                if (
                    ($arg['type'] == self::ARGUMENT_REQUIRED)
                    && ($arg['value'] === null)
                )
                {
                    throw new Exception("Missing argument: " . $arg['name'], 1);
                }
            }
        }

        if (method_exists(parent::class, 'configure'))
            parent::configure();

        $this->configure();

        # call the command handler if it exists or the main function
        if ($this->command['handler'])
            $result = $this->command['handler']($this);
        else
            $result = $this->main();

        return (
            is_int($result)
            ? $result
            : (
                is_null($result)
                ? self::SUCCESS
                : self::FAILED
            )
        );
    }

    /**
     * Main function of the command
     * Note: This function must be defined in the extended class, this function
     *       gets executed finally, so all of the logic related to options or
     *       associated arguments is defined here.
     */
    public function main()
    {
        # return the help by default
        return $this->help();
    }
}
