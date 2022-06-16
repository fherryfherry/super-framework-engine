<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;
use SuperFrameworkEngine\Helpers\ShellProcess;

class Migration extends Command
{
    use OutputMessage, CommandArguments;

    /**
     * @param $migrationName
     */
    public function migration($migrationName) {
        if(!file_exists(base_path("app/Migrations"))) mkdir(base_path("app/Migrations"));
        if(!file_exists(base_path("app/Migrations/Databases"))) mkdir(base_path("app/Migrations/Databases"));
        if(!file_exists(base_path("app/Migrations/Seeds"))) mkdir(base_path("app/Migrations/Seeds"));

        ShellProcess::run(base_path('vendor/bin/phinx').' create --parser=php --configuration='.base_path('configs/Phinx.php').' --path='.base_path('app/Migrations/Databases').' '.$migrationName);
    }

    public function migrate()
    {
        ShellProcess::run(base_path('vendor/bin/phinx').' migrate --parser=php --configuration='.base_path('configs/Phinx.php'));
    }

}