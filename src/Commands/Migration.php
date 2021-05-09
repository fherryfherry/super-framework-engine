<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;

class Migration extends Command
{
    use OutputMessage;

    /**
     * @param $migrationName
     * @throws \ReflectionException
     */
    public function makeMigration($migrationName) {
        if(!file_exists(base_path("app/Migrations"))) mkdir(base_path("app/Migrations"));
        if(!file_exists(base_path("app/Migrations/Databases"))) mkdir(base_path("app/Migrations/Databases"));
        if(!file_exists(base_path("app/Migrations/Seeds"))) mkdir(base_path("app/Migrations/Seeds"));

        $a = popen('cd '.base_path('vendor/bin').' && phinx create --parser=php --configuration=../../configs/Phinx.php --path=../../app/Migrations/Databases '.$migrationName, 'r');
        while($b = fgets($a, 2048)) {
            $this->success($b);
        }
        pclose($a);
    }

    public function migrate()
    {
        $a = popen('cd '.base_path('vendor/bin').' && phinx migrate --parser=php --configuration=../../configs/Phinx.php', 'r');
        while($b = fgets($a, 2048)) {
            $this->success($b);
        }
        pclose($a);
    }

}