<?php

declare(strict_types=1);

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

use PDO;
use PDOStatement;

class Driver
{
    public const AGGREGATE_TOTAL_FIELD = "total_records";
    public const AGGREGATE_COUNT_FUNC = "COUNT";
    public const AGGREGATE_SUM_FUNC = "SUM";
    public const AGGREGATE_MIN_FUNC = "MIN";
    public const AGGREGATE_MAX_FUNC = "MAX";
    public const AGGREGATE_AVG_FUNC = "AVG";

    public PDO $connection;
    public ?string $table = null;
    public string $select = "*";
    public ?array $where = null;
    public array $whereBinds = [];
    public ?int $limit = null;
    public ?int $offset = null;
    public ?string $order_by = null;
    public ?string $group_by = null;
    public ?string $having = null;
    public ?array $join = null;
    public ?array $join_type = null;
    public ?string $last_query = null;
    public string $selectQueryTemplate;
    public string $deleteQueryTemplate;
    public string $insertQueryTemplate;
    public string $updateQueryTemplate;
    public string $pdoQueryTemplate;
    public string $randomFuncTemplate;

    public function __construct(array $arguments)
    {
        if (count($arguments) > 0) {
            $this->connection = $arguments[0];
            $this->table = $arguments[1];
            $this->select = $arguments[2] ?? "*";
            $this->where = $arguments[3];
            $this->whereBinds = $arguments[4] ?? [];
            $this->limit = $arguments[5];
            $this->offset = $arguments[6];
            $this->order_by = $arguments[7];
            $this->group_by = $arguments[8];
            $this->having = $arguments[9];
            $this->join = $arguments[10];
            $this->join_type = $arguments[11];
        }

        $this->selectQueryTemplate = "SELECT {select} FROM {table} {join} {where} {group_by} {having} {order_by} {limit} {offset}";
        $this->deleteQueryTemplate = "DELETE FROM {table} {join} {where}";
        $this->insertQueryTemplate = "INSERT INTO {table} ({fields}) VALUES {values}";
        $this->updateQueryTemplate = "UPDATE {table} {join} SET {sets} {where}";
        $this->pdoQueryTemplate = "{driver}:host={host};dbname={database}";
        $this->randomFuncTemplate = "RAND()";
    }

    public static function createPDO(array $config): PDO
    {
        $query = str_replace(["{driver}", "{host}", "{database}"], [$config['driver'], $config['host'], $config['database']], (new static([]))->pdoQueryTemplate);
        return new PDO($query, $config['username'], $config['password']);
    }

    public function findPrimaryKey(string $table): ?string
    {
        if ($pk = get_singleton("findPrimaryKey_" . $table)) {
            return (string) $pk;
        }

        $query = $this->connection->query("DESCRIBE " . $table);
        if (!$query) {
            return null;
        }

        $query->setFetchMode(PDO::FETCH_ASSOC);
        $result = $query->fetchAll();
        foreach ($result as $row) {
            if ($row['Key'] === 'PRI') {
                put_singleton("findPrimaryKey_" . $table, $row['Field']);
                return (string) $row['Field'];
            }
        }
        return null;
    }

    public function _whereQuery(): string
    {
        return isset($this->where) ? "WHERE " . implode(" AND ", $this->where) : "";
    }

    public function _havingQuery(): string
    {
        return isset($this->having) ? "HAVING " . $this->having : "";
    }

    public function _orderByQuery(): string
    {
        return isset($this->order_by) ? "ORDER BY " . $this->order_by : "";
    }

    public function _groupByQuery(): string
    {
        return isset($this->group_by) ? "GROUP BY " . $this->group_by : "";
    }

    public function _offsetQuery(): string
    {
        return ($this->offset > 0) ? " OFFSET " . $this->offset : "";
    }

    public function _limitQuery(): string
    {
        return isset($this->limit) ? " LIMIT " . $this->limit : "";
    }

    public function queryBuilder(string $queryFor = "SELECT", ?string $aggregateField = null, bool $withOrderLimitGroup = true, bool $withJoin = true, bool $withCondition = true): string
    {
        $join_sql = $this->_joinQuery();
        $where_sql = $this->_whereQuery();
        $having_sql = $this->_havingQuery();
        $order_by_sql = $this->_orderByQuery();
        $group_by_sql = $this->_groupByQuery();
        $limit_sql = $this->_limitQuery();
        $offset_sql = $this->_offsetQuery();

        $this->last_query = match ($queryFor) {
            "SELECT" => str_replace(["{table}", "{select}"], [$this->table, $this->select], $this->selectQueryTemplate),
            "COUNT" => $this->buildAggregateQuery(self::AGGREGATE_COUNT_FUNC, $aggregateField),
            "SUM" => $this->buildAggregateQuery(self::AGGREGATE_SUM_FUNC, $aggregateField),
            "AVG" => $this->buildAggregateQuery(self::AGGREGATE_AVG_FUNC, $aggregateField),
            "MIN" => $this->buildAggregateQuery(self::AGGREGATE_MIN_FUNC, $aggregateField),
            "MAX" => $this->buildAggregateQuery(self::AGGREGATE_MAX_FUNC, $aggregateField),
            "DELETE" => str_replace(["{table}"], [$this->table], $this->deleteQueryTemplate),
            default => $this->selectQueryTemplate,
        };

        if ($withJoin) {
            $this->last_query = str_replace("{join}", $join_sql, $this->last_query);
        }
        if ($withCondition) {
            $this->last_query = str_replace("{where}", $where_sql, $this->last_query);
        }
        if ($withOrderLimitGroup) {
            $this->last_query = str_replace(
                ["{group_by}", "{having}", "{order_by}", "{limit}", "{offset}"],
                [$group_by_sql, $having_sql, $order_by_sql, $limit_sql, $offset_sql],
                $this->last_query
            );
        }

        $this->last_query = $this->cleansingQuery($this->last_query);
        return $this->last_query;
    }

    private function buildAggregateQuery(string $func, ?string $field): string
    {
        $field = $field ?? $this->table . '.' . $this->findPrimaryKey($this->table);
        $query = str_replace(["{table}"], [$this->table], $this->selectQueryTemplate);
        return str_replace(["{select}"], [$func . "(" . $field . ") as " . self::AGGREGATE_TOTAL_FIELD], $query);
    }

    public function hasColumn(string $table, string $column): bool
    {
        $columns = $this->listColumn($table);
        return in_array($column, $columns, true);
    }

    public function hasTable(string $table): bool
    {
        $tables = $this->listTable();
        return in_array($table, $tables, true);
    }

    public function _joinQuery(): string
    {
        $join_sql = "";
        if ($this->join) {
            foreach ($this->join as $i => $join) {
                $join_sql .= $this->join_type[$i] . " " . $join . " ";
            }
        }
        return $join_sql;
    }

    private function cleansingQuery(string $query): string
    {
        return str_replace(["{select}", "{join}", "{where}", "{group_by}", "{having}", "{order_by}", "{limit}", "{offset}"], "", $query);
    }

    public function update(array $array): PDOStatement
    {
        $sets = [];
        foreach ($array as $key => $value) {
            $sets[] = $key . "= ?";
        }
        $query = str_replace(["{table}", "{sets}", "{where}", "{join}"], [$this->table, implode(",", $sets), $this->_whereQuery(), $this->_joinQuery()], $this->updateQueryTemplate);
        $query = $this->cleansingQuery($query);
        $stmt = $this->connection->prepare($query);
        $execArray = array_values($array);
        $execArray = array_merge($execArray, $this->whereBinds);
        $stmt->execute($execArray);
        return $stmt;
    }

    public function insert(array $array): string
    {
        $this->insertBatch([$array]);
        return (string) $this->connection->lastInsertId($this->table);
    }

    public function insertBatch(array $array): ?string
    {
        if (count($array) === 0) {
            return null;
        }
        $fields = array_keys($array[0]);
        $values = [];
        for ($i = 0; $i < count($array); $i++) {
            $values[] = "(:" . implode($i . ",:", $fields) . $i . ")";
        }

        $query = str_replace(["{table}", "{fields}", "{values}"], [$this->table, implode(",", $fields), implode(",", $values)], $this->insertQueryTemplate);
        $query = $this->cleansingQuery($query);
        $stmt = $this->connection->prepare($query);
        $execArray = [];
        foreach ($array as $i => $item) {
            foreach ($item as $key => $val) {
                $execArray[":" . $key . $i] = $val;
            }
        }
        $stmt->execute($execArray);
        return (string) $this->connection->lastInsertId($this->table);
    }

    public function delete($id = null): void
    {
        if ($id) {
            $this->where[] = $this->table . "." . $this->findPrimaryKey($this->table) . " = ?";
            $this->whereBinds[] = $id;
        }

        $query = $this->queryBuilder("DELETE", null, false);
        $stmt = $this->connection->prepare($query);
        $stmt->execute($this->whereBinds);
    }

    public function find($id = null): mixed
    {
        $this->limit = 1;
        if ($id) {
            $this->where[] = $this->table . "." . $this->findPrimaryKey($this->table) . " = ?";
            $this->whereBinds[] = $id;
        }
        $stmt = $this->connection->prepare($this->queryBuilder("SELECT", null, true, true, true));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute($this->whereBinds);
        return $stmt->fetch();
    }

    public function all(): array
    {
        $stmt = $this->connection->prepare($this->queryBuilder());
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    public function paginate(): array
    {
        $page = request_int('page', 1);
        $this->offset = ($page - 1) * $this->limit;

        $query = $this->queryBuilder();
        $stmt = $this->connection->prepare($query);
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = [];
        $data['data'] = $stmt->fetchAll();
        $data['total'] = $this->count();
        return $data;
    }

    public function getLastQuery(): ?string
    {
        return $this->last_query;
    }

    public function count(): int
    {
        $stmt = $this->connection->prepare($this->queryBuilder("COUNT", null, false, true, true));
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return (int) ($result[self::AGGREGATE_TOTAL_FIELD] ?? 0);
    }

    public function sum(string $field): float
    {
        $stmt = $this->connection->prepare($this->queryBuilder("SUM", $field, false));
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return (float) ($result[self::AGGREGATE_TOTAL_FIELD] ?? 0);
    }

    public function max(string $field): mixed
    {
        $stmt = $this->connection->prepare($this->queryBuilder("MAX", $field, false));
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result[self::AGGREGATE_TOTAL_FIELD] ?? null;
    }

    public function min(string $field): mixed
    {
        $stmt = $this->connection->prepare($this->queryBuilder("MIN", $field, false));
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result[self::AGGREGATE_TOTAL_FIELD] ?? null;
    }

    public function avg(string $field): float
    {
        $stmt = $this->connection->prepare($this->queryBuilder("AVG", $field, false));
        $stmt->execute($this->whereBinds);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return (float) ($result[self::AGGREGATE_TOTAL_FIELD] ?? 0);
    }

    protected function sanitizeTableName(string $table): string
    {
        return $table;
    }

    public function listColumn(string $table): array
    {
        $table = $this->sanitizeTableName($table);
        $this->table = $table;
        $query = str_replace(["{select}", "{table}"], ["*", $table], $this->selectQueryTemplate);

        $this->limit = 0;
        $limitSql = $this->_limitQuery();
        $query = str_replace("{limit}", $limitSql, $query);
        $query = $this->clearQueryTemplate($query, ["join", "where", "having", "group_by", "order_by", "offset"]);

        $rs = $this->connection->query($query);
        if ($rs) {
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                $columns[] = $col['name'];
            }
        }
        return $columns;
    }

    public function listTable(): array
    {
        $query = $this->connection->query('SHOW TABLES');
        return $query ? $query->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    public function orderByRandom(): string
    {
        return $this->randomFuncTemplate;
    }

    private function clearQueryTemplate(string $query, array $keys): string
    {
        foreach ($keys as $i => $key) {
            $keys[$i] = "{" . $key . "}";
        }
        return str_replace($keys, "", $query);
    }
}
