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

    public function findPrimaryKey(string $table): ?string {
        $table = $this->sanitizeTableName($table);
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

    public function _limitQuery(): string {
        return (isset($this->limit))?" TOP ".htmlentities((string) $this->limit):"";
    }

    public function _offsetQuery(): string
    {
        return ($this->offset > 0) ? " OFFSET " . htmlentities((string) $this->offset) . " ROWS" : "";
    }

    public function listTable(): array
    {
        $query = $this->connection->query("SELECT TABLE_NAME 
        FROM ".config("database.database").".INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_TYPE = 'BASE TABLE'");
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

}