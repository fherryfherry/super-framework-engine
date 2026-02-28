<?php

declare(strict_types=1);

use SuperFrameworkEngine\App\UtilORM\ORM;

if (!function_exists("db")) {
    /**
     * @param string|null $table
     * @return ORM
     */
    function db(?string $table = null): ORM
    {
        return ORM::createConnection()->db($table);
    }
}
