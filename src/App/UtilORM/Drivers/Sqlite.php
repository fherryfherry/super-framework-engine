<?php

declare(strict_types=1);

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

use PDO;

class Sqlite extends Driver
{
    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        $this->randomFuncTemplate = "RANDOM()";
        $this->pdoQueryTemplate = "sqlite:{database}";
    }

    public function findPrimaryKey(string $table): ?string
    {
        $table = $this->sanitizeTableName($table);
        if ($pk = get_singleton("findPrimaryKey_" . $table)) {
            return (string) $pk;
        }

        $query = $this->connection->query("PRAGMA table_info(" . $table . ")");
        if (!$query) {
            return null;
        }

        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            if ($row['pk'] === 1) {
                put_singleton("findPrimaryKey_" . $table, $row['name']);
                return (string) $row['name'];
            }
        }
        return null;
    }

    public function listTable(): array
    {
        $query = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table'");
        return $query ? $query->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    public function _limitQuery(): string
    {
        return isset($this->limit) ? " LIMIT " . $this->limit : "";
    }

    public function _offsetQuery(): string
    {
        // SQLite requires LIMIT if OFFSET is used
        if (isset($this->offset) && !isset($this->limit)) {
            return " LIMIT -1 OFFSET " . $this->offset;
        }
        return isset($this->offset) ? " OFFSET " . $this->offset : "";
    }
}
