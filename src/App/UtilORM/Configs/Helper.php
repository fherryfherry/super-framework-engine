<?php

if(!function_exists("db")) {
    /**
     * @param $table
     * @return \SuperFrameworkEngine\App\UtilORM\ORM
     */
    function db($table) {
        return (new \SuperFrameworkEngine\App\UtilORM\ORM())->db($table);
    }
}