<?php

namespace IrfanTOOR;

use Exception;
use IrfanTOOR\Console;
use IrfanTOOR\Debug;

class Command
{
    /**
     * Version
     *
     * @var const
     */
    const VERSION = "0.3";


    /**
     * array of commands, if this is a composit command, added using addCommand function
     *
     * @var array
     */    
    protected $commands = [];

    /**
     * options associated with this command, added using addOption function 
     *
     * @var array
     */
    protected $options  = [];

    /**
     * operands associated with this command, added using addOperand function 
     *
     * @var array
     */    
    protected $operands = [];

    /**
     * Constructs the command
     *
     * @param string $name         name of the command to be displayed in help
     * @param string $description  description of the command to be displayed in help
     * @param mixed  $handler      optional: null or a closure, in case of null function main will be used as handler
     * @param string $version      optional: "0.1" by default
     * @param bool   $throw        throws exception or use builtin minimal exception handler
     */ 
    public function __construct($name, $description, $handler = null, $version = null, $throw = false)
    {
        $this->console = new Console();

        $this->name        = $name;
        $this->description = $description;

        if ($handler === null) {
            $handler = [$this, 'main'];
        }

        $this->handler     = $handler;

        if (!$version) {
            # Constants::VERSION of called class or this class
            $ca = explode('\\', get_called_class());
            $constants = '\\' . array_shift($ca) . '\\' . array_shift($ca);
            $constants = str_replace('\\\\', '\\', $constants);

            if (class_exists($constants)) {
                $version = $constants::VERSION;
            } else {
                $version = self::VERSION;
            }
        }

        $this->version = $version;

        # todo -- calculate the hash of the file containing this or the derived Command class
        # better yet find a way to include the git hash ;-)
        $class_hash =
            # md5(fileOfClass(
                get_called_class()
            # ))
            ;

        $this->version_hash = md5($this->name . $this->description . $this->version . $class_hash);

        if (!$throw) {
            set_exception_handler(function($obj){
                $this->exceptionHandler($obj);
            });
        }

        $this->addOption('v', 'verbose', 'Adds verbosity');
        $this->addOption('V', 'version', 'Displays version and quit');
        $this->addOption('h', 'help',    'Displays this help and quit');
    }

    /**
     * Used to simplyfy console calls
     * The console functions e.g. write or writeln etc. can be called using $this->writeln() notation
     */
    public function __call($method, $args)
    {
        if (is_callable([$this->console, $method])) {
            return call_user_func_array([$this->console, $method], $args);
        } else {
            throw new Exception("Command: Unknown Method '$method'");
        }
    }

    /**
     * Minimal exception handler
     */
    public function exceptionHandler($e)
    {
        $this->console->writeln([$e->getMessage()], ['bg_red', 'white']);
    }

    /**
     * Returns the name of this command
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the description of this command
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the version of this command
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the Base Path of this command
     *
     * @return string
     */
    public function getCmdBasePath()
    {
        $path = null;

        $parts = explode('\\', get_called_class());
        $class   = array_pop($parts);
        $files   = get_included_files();

        foreach($files as $file) {
            if (strpos($file, $class . '.php') !==false) {
                $pos  = strrpos($file, '/src/');
                $path = substr($file, 0, $pos + 1);
                break;
            }
        }

        return $path;
    }

    /**
     * Adds a command to this command, (Note: this command becomes a composite command)
     *
     * @param Command $command e.g. $this->addCommand(new HelloCommand());
     */
    public function addCommand(Command $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * Adds an option to this command
     *
     * @param char   $short       e.g. 'g'
     * @param string $long        e.g. 'greeting'
     * @param string $description description of this option to be displayed in help
     * @param const  $argument    ARGUMENT_NOT_REQUIRED | ARGUMENT_REQUIRED | ARGUMENT_OPTIONAL
     * @param string $default     default value for this option in case of ARGUMENT_OPTIONAL
     */
    public function addOption(
        $short, $long, $description,
        $argument = self::ARGUMENT_NOT_REQUIRED, $default = 0)
    {
        $k = strtolower($short . $long);

        $this->options[$k] = [
            'short'       => str_replace(':', '', $short),
            'long'        => str_replace(':', '', $long),
            'description' => $description,
            'argument'    => $argument,
            'value'       => ($argument === self::ARGUMENT_REQUIRED) ? 0 : $default,
        ];
    }

    /**
     * Adds an operand to this command
     *
     * @param string $name        e.g. 'name'
     * @param string $description description of this operand to be displayed in help
     * @param const  $argument    ARGUMENT_NOT_REQUIRED | ARGUMENT_REQUIRED | ARGUMENT_OPTIONAL
     * @param string $default     default value for this option in case of ARGUMENT_OPTIONAL
     */
    public function addOperand(
        $name, $description,
        $argument = self::ARGUMENT_REQUIRED, $default = '')
    {
        if (!
            (
                $argument == self::ARGUMENT_REQUIRED ||
                $argument == self::ARGUMENT_OPTIONAL
            )
        )
        {
            throw new Exception("Invalid value for argument", 1);
        }

        $this->operands[$name] = [
            'name'        => $name,
            'description' => $description,
            'argument'    => $argument,
            'value'       => ($argument === self::ARGUMENT_REQUIRED) ? '' : $default,
        ];
    }

    /**
     * Returns the count or value of an option
     *
     * @param string $name e.g. 'greeting'
     *
     * @return mixed int count of the option OR string value of the option if one was provided
     */
    public function getOption($name)
    {
        $found = false;
        foreach ($this->options as $k => $v) {
            extract($v);
            if ($short === $name || $long === $name) {
                $found = true;
                break;
            }
        }

        if (!$found)
            throw new Exception("Unknown option: " . $name, 1);

        return $value;
    }

    /**
     * Returns the value of an operand if present
     *
     * @param string  $name        e.g. 'name'
     *
     * @return string the value of the option if one was provided
     */
    public function getOperand($name)
    {
        if (array_key_exists($name, $this->operands)) {
            return $this->operands[$name]['value'];
        } else {
            throw new Exception("Unknown operand: " . $name, 1);
        }
    }

    /**
     * Prints the help of the command when an option -h or --help is gived on command line
     */
    public function help()
    {
        $this->writeln($this->name . ' ' . $this->version, ['bold', 'white']);
        $this->writeln($this->description);
        $this->writeln('');

        $this->write("usage: ");
        $usage = $this->name;

        if (count($this->commands))
            $usage .= ' <command>';

        $usage .= ' [options]';

        if (count($this->operands)) {
            $usage .= ' [--]';
            foreach($this->operands as $k => $v) {
                if ($v['argument'] == 1) {
                    $usage .= ' <' . $v['name'] . '>';
                } else {
                    $usage .= ' [<' . $v['name'] . '>]';
                }
            }
        }

        $this->writeln($usage);

        # Commands
        if ($this->commands) {
            echo PHP_EOL;

            $this->writeln("Commands:");
            $max = 0;

            foreach ($this->commands as $k => $v) {
                $max = max($max, strlen($k));
            }

            foreach ($this->commands as $k => $v) {
                $this->write(' ' . $k . str_repeat(' ', $max + 4 - strlen($k)), 'green');
                $this->writeln($v->getDescription(), 'yellow');
            }
        }

        # Options
        echo PHP_EOL;

        if ($this->options) {
            ksort($this->options);
            $this->writeln("Options:");

            $max = 0;
            foreach ($this->options as $k => $v) {
                extract($v);
                $l = max(strlen($short), 6) + strlen($long) + 2;
                $max = max($max, $l);
            }

            foreach ($this->options as $k => $v) {
                extract($v);

                $s1 = strlen($short) ?: 4;
                $s2 = $max - strlen($long) - 3;

                $sep1 = str_repeat(' ', $s1);
                $sep2 = str_repeat(' ', $s2);

                $this->write(
                    $sep1 . ($short ? '-' . $short : '') . # -s
                    ($short && $long ? ',' : '') .         # ,
                    ($long ? ' --' . $long : '') .         # --long
                    $sep2,                                 # space before description
                    'green');                              # color

                $this->write($description, 'yellow');    # description and color

                # print if the argument is required or optional and its default value if so
                if ($argument == self::ARGUMENT_REQUIRED) {
                    $this->write(' [required]', 'dark');
                } elseif ($argument == self::ARGUMENT_OPTIONAL) {
                    $this->write(' [optional, default: ' . print_r($value, 1) . ']', 'dark');
                }

                $this->writeln('');

            }

            echo PHP_EOL;
        }

        # Operands
        if (count($this->operands)) {
            $this->writeln('Operands:');

            $max = 0;
            foreach ($this->operands as $k => $v) {
                $max = max($max, strlen($k));
            }

            $max += 4;

            foreach ($this->operands as $operand) {
                extract($operand);

                $s = $max - strlen($name);
                $sep = str_repeat(' ', $s);

                $this->write(' ' . $name . $sep, 'green');
                $this->write($description, 'yellow');

                # print if the argument is required or optional and its default value if so
                if ($argument == self::ARGUMENT_REQUIRED) {
                    $this->write(' [required]', 'dark');
                } elseif ($argument == self::ARGUMENT_OPTIONAL) {
                    $this->write(' [optional, default: ' . print_r($value, 1) . ']', 'dark');
                }

                $this->writeln('');
            }
        }
    }

    /**
     * Runs a system command and returns the result
     *
     * @param string $command
     *
     * @return array output and exit_code are returned
     */

    public function system($command)
    {
        $command .= ' 2>&1';
        ob_start();
        system($command, $exit_code);
        $output = ob_get_clean();
        return compact(['output', 'exit_code']);
    }

    /**
     * Used to run the command. Parses the arguments and executes the provided 
     * handler or function main.
     *
     * @param mixed $args if no arguments are provided, the args list passed on
     *                    the command line is used else the provided array of 
     *                    arguments are parsed for options and operands.
     * @return mixed
     */
    public function run($args = null)
    {
        # process arguments
        if ($args === null) {
            $args = $_SERVER['argv'];
            array_shift($args);
        }

        $this->args = $args;

        $command  = count($this->commands) > 0;
        $options  = !$command;
        $operands = false;
        $stop     = false;

        # if a command is required and no argument is present run the main() rather
        # main(): by defaults shows the help
        if ((count($args) === 0) && $command) {
            $this->main();
            exit;
        }

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            if ($arg === '--') {
                $token = '--';
            } elseif (strpos($arg, '--') === 0) {
                $token = 'long';
                $arg = substr($arg, 2);
            } elseif (strpos($arg, '-') === 0) {
                $token = 'short';
                $arg = substr($arg, 1);
            } elseif ($command) {
                $token = 'command';
            } elseif ($operands) {
                $token = 'operand';
            } else {
                $token = 'arg';
            }

            switch ($token) {
                case 'command':
                    if (array_key_exists($arg, $this->commands)) {
                        array_shift($args);
                        $this->commands[$arg]->run($args);
                        exit;
                    } else {
                        throw new Exception("Unknown command: " . $arg, 1);
                    }
                    $command = false;
                    $i--;
                    break;

                case 'operand':
                case 'arg':
                    foreach ($this->operands as $k => $v) {
                        $this->operands[$k]['value'] = $arg;
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
                    $operands = true;
                    break;

                case 'short':
                    for ($l = 0; $l < strlen($arg); $l++) {
                        $a = $arg[$l];
                        $found = false;
                        foreach ($this->options as $k => $v) {
                            extract($v);
                            if ($short === $a) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            throw new Exception("Unknown option: " . $arg, 1);
                        }

                        if ($argument === self::ARGUMENT_REQUIRED) {
                            $this->options[$k]['value'] = substr($arg, $l + 1);
                            $l = strlen($arg);
                        } elseif ($argument === self::ARGUMENT_OPTIONAL) {
                            if ($arg[$l + 1] === '=') {
                                    $this->options[$k]['value'] = substr($arg, $l + 2);
                                    $l = strlen($arg);
                            } else {
                                throw new Exception("Missing required option: " . $arg, 1);
                            }
                        } else {
                            $this->options[$k]['value'] += 1;
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
                    foreach ($this->options as $k => $v) {
                        extract($v);
                        if ($long === $arg) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        throw new Exception("Unknown option: " . $arg, 1);
                    }

                    if ($argument === self::ARGUMENT_OPTIONAL) {
                        if ($arg_value != null) {
                            $this->options[$k]['value'] = $arg_value;
                        } else {
                            $this->options[$k]['value'] += 1;
                        }
                    } elseif ($argument === self::ARGUMENT_REQUIRED) {
                        if (isset($args[$i + 1])) {
                            $this->options[$k]['value'] = $args[$i + 1];
                            $i++;
                        } else {
                            throw new Exception("Missing required option: " . $arg, 1);
                        }
                    } else {
                        $this->options[$k]['value'] += 1;
                    }

                    break;
            }
        }

        # verbosity
        $this->verbose = $this->getOption('verbose');
        if ($this->verbose) {
            Debug::enable($this->verbose);
        }

        # version
        if ($this->getOption('version')) {
            // echo sprintf('%s: %s' . PHP_EOL, $this->name, $this->version);
            $this->writeln($this->name . ' v' . $this->version, ['bold', 'white']);
            $this->writeln('version hash: ' . $this->version_hash, 'yellow');
            exit;
        }

        # help
        if ($option = $this->getOption('help')) {
            $this->help($option);
            exit;
        }

        if (count($this->commands))
        {
            throw new Exception("Missing command", 1);
        }

        # check if all required options have been provided
        foreach ($this->options as $k => $v) {
            extract($v);
            if ($argument == self::ARGUMENT_REQUIRED && $value === 0) {
                if ($long)
                    throw new Exception("Missing option value: " . '--' . $long, 1);
                else
                    throw new Exception("Missing option value: " . '-' . $short, 1);
            }
        }

        # check if all required operands have been provided
        foreach ($this->operands as $k => $v) {
            extract($v);
            if ($argument == self::ARGUMENT_REQUIRED && $value === '') {
                throw new Exception("Missing operand: " . $name, 1);
            }
        }

        return call_user_func($this->handler, $this);
    }

    /**
     * If no handler is provided while constructing, this function is called.
     * This must be overridden in an extended class
     */
    public function main()
    {
        $this->help();
    }
}
