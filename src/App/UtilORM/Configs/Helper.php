<?php

$globalConnection = null;

if(!function_exists("db")) {
    /**
     * @param string|null $table
     * @return \SuperFrameworkEngine\App\UtilORM\ORM
     */
    function db(string $table = null) {
        global $globalConnection;
        if(isset($globalConnection)) {
            return (new \SuperFrameworkEngine\App\UtilORM\ORM($globalConnection))->db($table);
        } else {
            $globalConnection = \SuperFrameworkEngine\App\UtilORM\ORM::createConnection();
            return (new \SuperFrameworkEngine\App\UtilORM\ORM($globalConnection))->db($table);
        }
    }
}