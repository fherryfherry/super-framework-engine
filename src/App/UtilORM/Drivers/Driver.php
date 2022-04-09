<?php

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

class Driver
{
    const AGGREGATE_TOTAL_FIELD = "total_records";
    const AGGREGATE_COUNT_FUNC = "COUNT";
    const AGGREGATE_SUM_FUNC = "SUM";
    const AGGREGATE_MIN_FUNC = "MIN";
    const AGGREGATE_MAX_FUNC = "MAX";
    const AGGREGATE_AVG_FUNC = "AVG";

    public $connection;
    public $table;
    public $select;
    public $where;
    public $whereBinds;
    public $limit;
    public $offset;
    public $order_by;
    public $group_by;
    public $having;
    public $join;
    public $join_type;
    public $last_query;
    public $selectQueryTemplate;
    public $insertQueryTemplate;
    public $updateQueryTemplate;
    public $deleteQueryTemplate;
    public $pdoQueryTemplate;
    public $randomFuncTemplate;

    public function __construct(array $arguments)
    {
        if(count($arguments)) {
            $this->connection = $arguments[0];
            $this->table = $arguments[1];
            $this->select = $arguments[2];
            $this->where = $arguments[3];
            $this->whereBinds = $arguments[4]?:[];
            $this->limit = $arguments[5];
            $this->offset = $arguments[6];
            $this->order_by = $arguments[7];
            $this->group_by = $arguments[8];
            $this->having = $arguments[9];
            $this->join = $arguments[10];
            $this->join_type = $arguments[11];
        }

        $this->selectQueryTemplate = "SELECT {select} FROM `{table}` {join} {where} {group_by} {having} {order_by} {limit} {offset}";
        $this->deleteQueryTemplate = "DELETE FROM `{table}` {join} {where}";
        $this->insertQueryTemplate = "INSERT INTO ({fields}) VALUES {values}";
        $this->updateQueryTemplate = "UPDATE `{table}` {join} SET {sets} {where}";
        $this->pdoQueryTemplate = "{driver}:host={host};dbname={database}";
        $this->randomFuncTemplate = "RAND()";
    }

    public static function createPDO(array $config)
    {
        $query = str_replace(["{driver}","{host}","{database}"],[$config['driver'],$config['host'],$config['database']], (new static([]))->pdoQueryTemplate);
        return new \PDO($query, $config['username'], $config['password']);
    }

    public function findPrimaryKey($table) {
        if($pk = get_singleton("findPrimaryKey_".$table)) {
            return $pk;
        } else {
            $query = $this->connection->query("DESCRIBE ".$table);
            $query->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $query->fetchAll();
            foreach($result as $row) {
                if($row['Key'] == 'PRI') {
                    put_singleton("findPrimaryKey_".$table,$row['Field']);
                    return $row['Field'];
                }
            }
            return null;
        }
    }


    public function _whereQuery() {
        return (isset($this->where))?"WHERE ".implode(" AND ",$this->where):"";
    }

    public function _havingQuery() {
        return (isset($this->having))?"HAVING ".implode(" AND ", $this->having) : "";
    }

    public function _orderByQuery() {
        return (isset($this->order_by))?"ORDER BY ".htmlentities($this->order_by):"";
    }

    public function _groupByQuery() {
        return (isset($this->group_by)) ? "GROUP BY ".htmlentities($this->group_by): "";
    }

    public function _offsetQuery() {
        return (isset($this->offset))?" OFFSET ".htmlentities($this->offset):"";
    }

    public function _limitQuery() {
        return (isset($this->limit))?" LIMIT ".htmlentities($this->limit):"";
    }

    /**
     * @param string $queryFor
     * @param bool $withOrderLimitGroup
     * @param bool $withJoin
     * @param bool $withCondition
     * @return string
     */
    private function queryBuilder(string $queryFor = "SELECT", string $aggregateField = null, bool $withOrderLimitGroup = true, bool $withJoin = true, bool $withCondition = true) {

        $join_sql = $this->_joinQuery();
        $where_sql = $this->_whereQuery();
        $having_sql = $this->_havingQuery();
        $order_by_sql = $this->_orderByQuery();
        $group_by_sql = $this->_groupByQuery();
        $limit_sql  = $this->_limitQuery();
        $offset_sql = $this->_offsetQuery();

        switch ($queryFor) {
            case "SELECT":
                // Init last query
                $this->last_query = $this->selectQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                break;
            case "COUNT":
                $field = ($aggregateField)?:$this->findPrimaryKey($this->table);
                $field = htmlentities($field);

                // Init last query
                $this->last_query = $this->selectQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                // Replace aggregate func
                $this->last_query = str_replace(["{select}"],[self::AGGREGATE_COUNT_FUNC."(".$field.")"],$this->last_query);
                break;
            case "SUM":
                $field = htmlentities($aggregateField);
                // Init last query
                $this->last_query = $this->selectQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                // Replace aggregate func
                $this->last_query = str_replace(["{select}"],[self::AGGREGATE_SUM_FUNC."(".$field.")"],$this->last_query);
                break;
            case "AVG":
                $field = htmlentities($aggregateField);
                // Init last query
                $this->last_query = $this->selectQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                // Replace aggregate func
                $this->last_query = str_replace(["{select}"],[self::AGGREGATE_AVG_FUNC."(".$field.")"],$this->last_query);
                break;
            case "MIN":
                $field = htmlentities($aggregateField);
                // Init last query
                $this->last_query = $this->selectQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                // Replace aggregate func
                $this->last_query = str_replace(["{select}"],[self::AGGREGATE_MIN_FUNC."(".$field.")"],$this->last_query);
                break;
            case "MAX":
                $field = htmlentities($aggregateField);
                // Init last query
                $this->last_query = $this->selectQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                // Replace aggregate func
                $this->last_query = str_replace(["{select}"],[self::AGGREGATE_MAX_FUNC."(".$field.")"],$this->last_query);
                break;
            case "DELETE":
                // Init last query
                $this->last_query = $this->deleteQueryTemplate;
                // Replace table
                $this->last_query = str_replace(["{table}"],[$this->table],$this->last_query);
                break;
        }

        if($withJoin) {
            $this->last_query = str_replace("{join}",$join_sql,$this->last_query);
        }
        if($withCondition) {
            $this->last_query = str_replace("{where}",$where_sql,$this->last_query);
        }
        if($withOrderLimitGroup) {
            // Replace group by
            $this->last_query = str_replace("{group_by}",$group_by_sql,$this->last_query);
            // Replace having
            $this->last_query = str_replace("{having}",$having_sql,$this->last_query);
            // Replace order
            $this->last_query = str_replace("{order_by}",$order_by_sql,$this->last_query);
            // Replace Limit
            $this->last_query = str_replace("{limit}",$limit_sql,$this->last_query);
            // Replace offset
            $this->last_query = str_replace("{offset}",$offset_sql,$this->last_query);
        }
        return $this->last_query;
    }

    /**
     * @param $table
     * @param $column
     * @return bool
     */
    public function hasColumn($table, $column) {
        $columns = $this->listColumn($table);
        return in_array($column, $columns);
    }

    /**
     * To check database has a table or not
     * @param $table
     * @return bool
     */
    public function hasTable($table) {
        $tables = $this->listTable();
        return in_array($table, $tables);
    }

    /**
     * @return string
     */
    private function _joinQuery() {
        $join_sql = "";
        if($this->join) {
            foreach($this->join as $i=>$join) {
                $join_sql .= $this->join_type[$i]." ".$join." ";
            }
        }
        return $join_sql;
    }

    /**
     * To update a record
     * @param array $array
     * @return false|\PDOStatement
     */
    public function update(array $array) {
        $sets = [];
        foreach($array as $key=>$value) {
            $sets[] = $key."= :".$key;
        }
        $query = $this->updateQueryTemplate;
        $query = str_replace(["{table}","{sets}","{where}","{join}"],[$this->table,implode(",",$sets),$this->_whereQuery(),$this->_joinQuery()],$query);
        $stmt = $this->connection->prepare($query);
        $execArray = [];
        foreach($array as $key => $val) {
            $execArray[":" . $key] = $val;
        }
        $stmt->execute($execArray);
        return $stmt;
    }

    /**
     * To insert a record
     * @param array $array
     * @return string
     */
    public function insert(array $array) {
        $this->insertBatch([$array]);
        return $this->connection->lastInsertId($this->table);
    }

    /**
     * To insert batch record
     * @param array $array
     * @return string
     */
    public function insertBatch(array $array): ?string
    {
        if(!count($array)) {
            return null;
        }
        $fields = array_keys($array[0]);
        $total = count($fields);
        $values = [];
        for($i=0;$i<$total;$i++) {
            $values[] = "(".implode(",:",$fields).")";
        }

        $query = $this->insertQueryTemplate;
        $query = str_replace(["{table}","{fields}","{values}"],[$this->table,implode(",",$fields),implode(",", $values)],$query);
        $stmt = $this->connection->prepare($query);
        $execArray = [];
        foreach($array as $i=>$item) {
            foreach($item as $key => $val) {
                $execArray[":" . $key.$i] = $val;
            }
        }
        $stmt->execute($execArray);
        return $this->connection->lastInsertId($this->table);
    }

    /**
     * To delete a record
     * @param null $id
     * @return false|int
     */
    public function delete($id = null) {

        if($id) {
            $id = htmlentities($id);
            $this->where[] = $this->table.".".$this->findPrimaryKey($this->table)." = ?";
            $this->whereBinds[] = $id;
        }

        return $this->connection->exec($this->queryBuilder("DELETE", null, false));
    }

    public function find($id = null) {
        $this->limit = 1;
        if($id) {
            $this->where[] = $this->table.".".$this->findPrimaryKey($this->table)." = ?";
            $this->whereBinds[] = $id;
        }
        $stmt = $this->connection->query($this->queryBuilder("SELECT", null, false, true, true));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch();
    }

    public function all() {
        $stmt = $this->connection->prepare($this->queryBuilder());
        $stmt->execute(array_merge(...$this->whereBinds));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }


    public function paginate() {

        $where_binds = array_merge(...$this->whereBinds);
        $page = request_int('page', 1);
        $this->offset = ($page - 1) * $this->limit;

        $stmt = $this->connection->prepare($this->queryBuilder());
        $stmt->execute($where_binds);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $data = [];
        // Fetch Data
        $data['data'] = $stmt->fetchAll();

        // Get Total
        $data['total'] = $this->count();
        return $data;
    }

    public function getLastQuery()
    {
        return $this->last_query;
    }

    public function count() {
        $stmt = $this->connection->prepare($this->queryBuilder("COUNT", null,false, true, true));
        $stmt->execute(array_merge(...$this->whereBinds));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()[self::AGGREGATE_TOTAL_FIELD];
    }

    public function sum($field) {
        $stmt = $this->connection->prepare($this->queryBuilder("SUM", $field, false));
        $stmt->execute(array_merge(...$this->whereBinds));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()[self::AGGREGATE_TOTAL_FIELD];
    }

    public function max($field) {
        $stmt = $this->connection->prepare($this->queryBuilder("MAX", $field, false));
        $stmt->execute(array_merge(...$this->whereBinds));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()[self::AGGREGATE_TOTAL_FIELD];
    }

    public function min($field) {
        $stmt = $this->connection->prepare($this->queryBuilder("MIN", $field, false));
        $stmt->execute(array_merge(...$this->whereBinds));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()[self::AGGREGATE_TOTAL_FIELD];
    }

    public function avg($field) {
        $stmt = $this->connection->prepare($this->queryBuilder("AVG", $field, false));
        $stmt->execute(array_merge(...$this->whereBinds));
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()[self::AGGREGATE_TOTAL_FIELD];
    }

    public function listColumn($table) {
        $columns = [];
        $query = $this->selectQueryTemplate;
        $query = str_replace(["{select}","{table}"],["*",$table],$query);

        $this->limit = 0;
        $limitSql = $this->_limitQuery();
        $query = str_replace("{limit}",$limitSql,$query);

        // Clear Other Keys
        $query = $this->clearQueryTemplate($query,["join","where","having","group_by","order_by","offset"]);

        $rs = $this->connection->query($query);
        for ($i = 0; $i < $rs->columnCount(); $i++) {
            $col = $rs->getColumnMeta($i);
            $columns[] = $col['name'];
        }
        return $columns;
    }

    public function listTable()
    {
        $query = $this->connection->query('SHOW TABLES');
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function orderByRandom() {
        return $this->randomFuncTemplate;
    }

    private function clearQueryTemplate(string $query, array $keys) {
        foreach($keys as $i=>$key) {
            $keys[$i] = "{".$key."}";
        }
        return str_replace($keys,"", $query);
    }
}