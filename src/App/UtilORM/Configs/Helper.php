<?php

if(!function_exists("db")) {
    /**
     * @param string|null $table
     * @return \SuperFrameworkEngine\App\UtilORM\ORM
     */
    function db(string $table = null) {
        return \SuperFrameworkEngine\App\UtilORM\ORM::createConnection()->db($table);
    }
}