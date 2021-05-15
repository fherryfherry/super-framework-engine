<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;

class Schedule extends Command
{
    use OutputMessage;

    public function run() {
        $a = popen('cd '.base_path('vendor/bin').' && crunz schedule:run', 'r');
        while($b = fgets($a, 2048)) {
            $this->success($b);
        }
        pclose($a);
    }
}