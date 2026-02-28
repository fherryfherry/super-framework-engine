<?php

declare(strict_types=1);

namespace SuperFrameworkEngine\App\UtilORM;

use PDO;
use SuperFrameworkEngine\App\UtilORM\Drivers\Mysql;
use SuperFrameworkEngine\App\UtilORM\Drivers\Pgsql;
use SuperFrameworkEngine\App\UtilORM\Drivers\Sqlite;
use SuperFrameworkEngine\App\UtilORM\Drivers\Sqlsrv;
use SuperFrameworkEngine\App\UtilORM\Drivers\Driver;

class ORM
{
    private array $config;
    private PDO $connection;
    private ?string $table = null;
    private string $select = "*";
    private ?array $where = null;
    private ?array $whereBinds = null;
    private ?array $join = null;
    private ?array $join_type = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $order_by = null;
    private ?string $group_by = null;
    private ?string $having = null;
    private array $with = [];
    private static ?PDO $dbConn = null;

    private ?int $cacheTTL = null;

    public function __construct(PDO $connection)
    {
        $this->config = include base_path("configs/Database.php");
        $this->connection = $connection;
    }

    public function remember(int $seconds): self
    {
        $this->cacheTTL = $seconds;
        return $this;
    }

    public static function createConnection(): self
    {
        $config = include base_path("configs/Database.php");
        if (self::$dbConn === null) {
            self::$dbConn = match ($config['driver']) {
                'sqlsrv' => Sqlsrv::createPDO($config),
                'pgsql' => Pgsql::createPDO($config),
                'sqlite' => Sqlite::createPDO($config),
                default => Mysql::createPDO($config),
            };

            if ($config['driver'] !== 'sqlite') {
                self::$dbConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            }
            self::$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return new self(self::$dbConn);
    }

    public static function beginTransaction(): void
    {
        self::createConnection()->connection->beginTransaction();
    }

    public static function commit(): void
    {
        self::createConnection()->connection->commit();
    }

    public static function rollback(): void
    {
        self::createConnection()->connection->rollback();
    }

    public function getInstance(): PDO
    {
        return $this->connection;
    }

    private function driver(): Driver
    {
        $arguments = [
            $this->connection, $this->table, $this->select, $this->where,
            $this->whereBinds, $this->limit, $this->offset, $this->order_by,
            $this->group_by, $this->having, $this->join, $this->join_type
        ];

        return match ($this->config['driver'] ?? 'mysql') {
            'sqlsrv' => new Sqlsrv($arguments),
            'pgsql' => new Pgsql($arguments),
            'sqlite' => new Sqlite($arguments),
            default => new Mysql($arguments),
        };
    }

    public function findPrimaryKey(string $table): ?string
    {
        return $this->driver()->findPrimaryKey($table);
    }

    public function hasTable(string $table): bool
    {
        $cacheKey = "hasTable_" . $table;
        if ($exist = get_singleton($cacheKey)) {
            return (bool) $exist;
        }

        $exist = $this->driver()->hasTable($table);
        put_singleton($cacheKey, $exist);
        return $exist;
    }

    public function hasColumn(string $table, string $column): bool
    {
        $cacheKey = "hasColumn_" . $table . "_" . $column;
        if ($exist = get_singleton($cacheKey)) {
            return (bool) $exist;
        }

        $exist = $this->driver()->hasColumn($table, $column);
        put_singleton($cacheKey, $exist);
        return $exist;
    }

    public function listColumn(string $table): array
    {
        return $this->driver()->listColumn($table);
    }

    public function listTable(): array
    {
        return $this->driver()->listTable();
    }

    public function getLastQuery(): ?string
    {
        return $this->driver()->getLastQuery();
    }

    public function db(?string $table = null): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(string ...$fields): self
    {
        $this->select = implode(",", $fields);
        return $this;
    }

    public function addSelect(string $fieldName): self
    {
        if ($this->select === "*") {
            $this->select = $fieldName;
        } else {
            $this->select .= "," . $fieldName;
        }
        return $this;
    }

    public function addSelectTable(string $tableName, array $columnException = [], ?string $joinAlias = null): self
    {
        $tableColumns = $this->listColumn($tableName);
        $result = [];
        foreach ($tableColumns as $column) {
            $alias = $joinAlias ?: $tableName;
            if (count($columnException) > 0) {
                if (!in_array($column, $columnException, true)) {
                    $result[] = $alias . "." . $column . " as " . $alias . "_" . $column;
                }
            } else {
                $result[] = $alias . "." . $column . " as " . $alias . "_" . $column;
            }
        }

        if ($this->select === "*") {
            $this->select = implode(",", $result);
        } else {
            $this->select .= "," . implode(",", $result);
        }
        return $this;
    }

    public function join(string $join, string $joinType = "INNER JOIN"): self
    {
        $this->join[] = $join;
        $this->join_type[] = $joinType;
        return $this;
    }

    public function leftJoin(string $joinSql): self
    {
        return $this->join($joinSql, "LEFT JOIN");
    }

    public function rightJoin(string $joinSql): self
    {
        return $this->join($joinSql, "RIGHT JOIN");
    }

    public function outerJoin(string $joinSql): self
    {
        return $this->join($joinSql, "OUTER JOIN");
    }

    public function with(string ...$relations): self
    {
        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    public function where(string $whereQuery, ?array $bindValues = null): self
    {
        $this->where[] = $whereQuery;
        if ($bindValues !== null) {
            $this->whereBinds = ($this->whereBinds) ? array_merge($this->whereBinds, $bindValues) : $bindValues;
        }
        return $this;
    }

    public function whereNull(string $field): self
    {
        $this->where[] = $field . " IS NULL";
        return $this;
    }

    public function whereNotNull(string $field): self
    {
        $this->where[] = $field . " IS NOT NULL";
        return $this;
    }

    public function whereIn(string $field, array $array): self
    {
        if (count($array) === 0) {
            return $this->where("1=0");
        }
        $placeholders = implode(',', array_fill(0, count($array), '?'));
        return $this->where($field . " IN ($placeholders)", $array);
    }

    public function whereNotIn(string $field, array $array): self
    {
        if (count($array) === 0) {
            return $this;
        }
        $placeholders = implode(',', array_fill(0, count($array), '?'));
        return $this->where($field . " NOT IN ($placeholders)", $array);
    }

    public function whereDate(string $field, string $value): self
    {
        return $this->where("DATE(" . $field . ")=?", [$value]);
    }

    public function whereLike(string $field, string $keyword): self
    {
        return $this->where($field . " LIKE ?", ["%$keyword%"]);
    }

    public function orderBy(string $orderBy): self
    {
        $this->order_by = $orderBy;
        return $this;
    }

    public function groupBy(string $groupBy): self
    {
        $this->group_by = $groupBy;
        return $this;
    }

    public function having(string $having): self
    {
        $this->having = $having;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function raw(string $sql, array $binds = []): mixed
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($binds);
        return $stmt;
    }

    public function insert(array $array): string
    {
        return $this->driver()->insert($array);
    }

    public function update(array $array): bool
    {
        $this->driver()->update($array);
        return true;
    }

    public function delete($id = null): bool
    {
        $this->driver()->delete($id);
        return true;
    }

    public function find(mixed $id = null): mixed
    {
        $cacheKey = $this->getCacheKey("find_" . $id);
        if ($this->cacheTTL !== null && $cached = get_singleton($cacheKey)) {
            return $cached;
        }

        $result = $this->driver()->find($id);
        if ($result && count($this->with) > 0) {
            $result = $this->loadRelations([$result])[0];
        }

        if ($this->cacheTTL !== null && $result) {
            put_singleton($cacheKey, $result);
        }

        return $result;
    }

    public function first(): mixed
    {
        return $this->find();
    }

    public function all(?int $limit = null, int $offset = 0): array
    {
        if ($limit !== null) {
            $this->limit = $limit;
        }

        if ($offset > 0) {
            $this->offset = $offset;
        }

        $cacheKey = $this->getCacheKey("all_" . $limit . "_" . $offset);
        if ($this->cacheTTL !== null && $cached = get_singleton($cacheKey)) {
            return $cached;
        }

        $results = $this->driver()->all();
        if (count($results) > 0 && count($this->with) > 0) {
            $results = $this->loadRelations($results);
        }

        if ($this->cacheTTL !== null && count($results) > 0) {
            put_singleton($cacheKey, $results);
        }

        return $results;
    }

    private function getCacheKey(string $suffix): string
    {
        $query = $this->driver()->queryBuilder();
        return "orm_cache_" . md5($query . serialize($this->whereBinds) . $suffix);
    }

    public function get(?int $limit = null, int $offset = 0): array
    {
        return $this->all($limit, $offset);
    }

    public function count(): int
    {
        return $this->driver()->count();
    }

    public function sum(string $field): float
    {
        return $this->driver()->sum($field);
    }

    public function max(string $field): mixed
    {
        return $this->driver()->max($field);
    }

    public function min(string $field): mixed
    {
        return $this->driver()->min($field);
    }

    public function avg(string $field): float
    {
        return $this->driver()->avg($field);
    }

    private function loadRelations(array $results): array
    {
        // Simple eager loading implementation
        foreach ($this->with as $relation) {
            $foreignKey = $relation . "_id";
            $ids = array_unique(array_column($results, $foreignKey));
            if (count($ids) === 0) {
                continue;
            }

            $relatedData = (new self($this->connection))
                ->db($relation)
                ->whereIn('id', $ids)
                ->all();

            $indexedRelated = [];
            foreach ($relatedData as $row) {
                $indexedRelated[$row['id']] = $row;
            }

            foreach ($results as &$result) {
                $result[$relation] = $indexedRelated[$result[$foreignKey]] ?? null;
            }
        }
        return $results;
    }

    public function paginate(int $limit): array
    {
        $this->limit($limit);
        $data = $this->driver()->paginate();
        $page = request_int('page', 1);
        $data['links'] = $this->paginationHTML($page, $data['total'], $limit);
        $data['last_page'] = (int) ceil($data['total'] / $limit);

        if (count($data['data']) > 0 && count($this->with) > 0) {
            $data['data'] = $this->loadRelations($data['data']);
        }

        return $data;
    }

    private function paginationHTML(int $page, int $total, int $limit): string
    {
        $totalPages = (int) ceil($total / $limit);
        $result = "<ul class='pagination'>";

        // First & Prev
        if ($page === 1) {
            $result .= "<li class='disabled'><a href='#'>First</a></li><li class='disabled'><a href='#'>&laquo;</a></li>";
        } else {
            $result .= "<li><a href='" . get_current_url(['page' => 1]) . "'>First</a></li>";
            $result .= "<li><a href='" . get_current_url(['page' => $page - 1]) . "'>&laquo;</a></li>";
        }

        // Numbers
        $range = 3;
        $start = max(1, $page - $range);
        $end = min($totalPages, $page + $range);

        for ($i = $start; $i <= $end; $i++) {
            $active = ($page === $i) ? "class='active'" : "";
            $result .= "<li $active><a href='" . get_current_url(['page' => $i]) . "'>$i</a></li>";
        }

        // Next & Last
        if ($page === $totalPages || $totalPages === 0) {
            $result .= "<li class='disabled'><a href='#'>&raquo;</a></li><li class='disabled'><a href='#'>Last</a></li>";
        } else {
            $result .= "<li><a href='" . get_current_url(['page' => $page + 1]) . "'>&raquo;</a></li>";
            $result .= "<li><a href='" . get_current_url(['page' => $totalPages]) . "'>Last</a></li>";
        }

        $result .= "</ul>";
        return $result;
    }
}
