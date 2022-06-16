<?php

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

use Exception;

class Pgsql extends Driver
{

    /**
     * @param string $table
     * @return false|mixed|null
     */
    public function findPrimaryKey(string $table) {
        $query = $this->connection->query("SELECT a.attname AS name, format_type(a.atttypid, a.atttypmod) AS type
FROM
    pg_class AS c
    JOIN pg_index AS i ON c.oid = i.indrelid AND i.indisprimary
    JOIN pg_attribute AS a ON c.oid = a.attrelid AND a.attnum = ANY(i.indkey)
WHERE c.oid = '{$table}'::regclass");
        $query->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $query->fetch();
        return $result['name'];
    }

}