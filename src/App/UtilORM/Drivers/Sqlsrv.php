<?php

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

use Exception;

class Sqlsrv extends Driver
{

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);

        $this->pdoQueryTemplate = "{driver}:Server={host};Database={database};ConnectionPooling=0";
        $this->selectQueryTemplate = "SELECT {limit} {select} FROM {table} {join} {where} {group_by} {having} {order_by} {offset}";
        $this->randomFuncTemplate = "NEWID()";
    }

    public function findPrimaryKey($table) {
        if($pk = get_singleton("findPrimaryKey_".$table)) {
            return $pk;
        } else {
            $query = $this->connection->query("select COLUMN_NAME 
            from information_schema.KEY_COLUMN_USAGE 
            where CONSTRAINT_NAME='PRIMARY' AND TABLE_NAME='$table' 
            AND TABLE_SCHEMA='".config("database.database")."'");
            $query->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $query->fetch();
            return $result['COLUMN_NAME'] ?: "id";
        }
    }

    public function _limitQuery() {
        return (isset($this->limit))?" TOP ".htmlentities($this->limit):"";
    }

    public function _offsetQuery()
    {
        return (isset($this->offset))?" OFFSET ".htmlentities($this->offset)." ROWS":"";
    }

    public function listTable()
    {
        $query = $this->connection->query("SELECT TABLE_NAME 
        FROM ".config("database.database").".INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_TYPE = 'BASE TABLE'");
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

}