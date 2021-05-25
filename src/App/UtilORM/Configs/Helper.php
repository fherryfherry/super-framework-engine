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
            /** @var \SuperFrameworkEngine\App\UtilORM\ORM $globalConnection */
            return $globalConnection->db($table);
        } else {
            $globalConnection = \SuperFrameworkEngine\App\UtilORM\ORM::createConnection();
            $globalConnection = new \SuperFrameworkEngine\App\UtilORM\ORM($globalConnection);
            return $globalConnection->db($table);
        }
    }
}