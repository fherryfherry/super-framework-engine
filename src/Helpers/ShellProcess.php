<?php


namespace SuperFrameworkEngine\Helpers;


use SuperFrameworkEngine\Commands\OutputMessage;

class ShellProcess
{
    use OutputMessage;

    public static function run(string $command)
    {
        $a = popen($command, 'r');
        while($b = fgets($a, 2048)) {
            (new self())->success($b);
        }
        pclose($a);
    }

    public static function phpRun(string $command)
    {
        $a = popen(PHP_BINARY.' '.$command, 'r');
        while($b = fgets($a, 2048)) {
            (new self())->success($b);
        }
        pclose($a);
    }
}