<?php

namespace SuperFrameworkEngine\Commands;


use Dotenv\Dotenv;

class CommandRunner
{
    use OutputMessage, CommandCore, CommandArguments;

    private $bootstrap;

    public function __construct()
    {
        $this->bootstrap = include base_path("bootstrap/cache.php");
        try {
            Dotenv::createImmutable(base_path())->load();
        } catch (\Exception $e) {

        }
    }

    private function loadHelpers() {
        foreach($this->bootstrap['helper'] as $helper) {
            $helperPath = $helper['path'];
            $filePath = lcfirst(str_replace("\\",DIRECTORY_SEPARATOR,$helper['path'])).".php";
            require_once base_path($filePath);
        }
    }

    private function header()
    {
        $this->warning("SUPER FRAMEWORK CLI TOOL");
        $this->warning("------------------------");
        $this->warning("Date & Time : " . date('Y-m-d H:i:s'));
        $this->warning("Your PHP Version: ".phpversion());
        $this->warning("------------------------");
    }

    /**
     * @param $argv
     * @throws \ReflectionException
     */
    public function run($argv) {
        $this->loadHelpers();

        if(!$this->getArgument($argv,"ignore-header")) {
            $this->header();
        }

        try {
            $command = isset($argv[1]) ? $argv[1] : null;
            $commands = $this->getListCommand($this->bootstrap['command']);
            if($command) {
                $this->matcher($commands, $command, $argv);
            } else {
                $this->warning("Usage:");
                $this->defaultForeground("\tphp super [command] [arguments]\n");

                $this->warning("Available commands: ");
                foreach($commands as $c) {
                    $this->success("\t" . $c['command'], true);
                    $this->defaultForeground("\t\t".$c['description']);
                }
            }
        } catch (\Throwable $e) {
            logging($e);
            $this->warning("Something went wrong, please check log file");
        }
    }

    private function matcher($commands, $command, $arguments)
    {
        $arguments = (isset($arguments) && count($arguments) > 1) ? array_slice($arguments,2) : null;
        foreach($commands as $c) {
            if($c['command'] == $command) {
                $class = $c['class'];
                $method = $c['method'];
                if($arguments) {
                    call_user_func_array([new $class, $method], $arguments);
                } else {
                    (new $class)->$method();
                }
            }
        }
    }

}