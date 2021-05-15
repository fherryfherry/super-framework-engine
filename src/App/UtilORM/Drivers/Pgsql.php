<?php

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

use Exception;

class Pgsql
{
    private $connection;
    private $table;
    private $select;
    private $where;
    private $limit;
    private $offset;
    private $order_by;
    private $group_by;
    private $having;
    private $join;
    private $join_type;
    private $last_query;

    public function __construct(\PDO $connection, $table, $select, $join, $join_type, $where, $limit, $offset, $order_by, $group_by, $having)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->select = $select;
        $this->where = $where;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->order_by = $order_by;
        $this->group_by = $group_by;
        $this->having = $having;
        $this->join = $join;
        $this->join_type = $join_type;
    }

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
    public function hasTable(string $table) {
        try {
            $table = filter_var($table,FILTER_SANITIZE_STRING);
            $result = $this->connection->exec("SELECT EXISTS (
               SELECT FROM information_schema.tables 
               WHERE table_name   = '{$table}'
               );");
        } catch (Exception $e) {
            return FALSE;
        }
        return $result !== FALSE;
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
        $where_sql = (isset($this->where))?"WHERE ".implode(" AND ",$this->where):"";
        $query = "UPDATE `".$this->table."` SET ".implode(",",$sets)." ".$where_sql;
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
        $fields = array_keys($array);
        $stmt = $this->connection->prepare("INSERT INTO `".$this->table."` (".implode(",", $fields).") VALUES (:".implode(",:", $fields).")");
        $execArray = [];
        foreach($array as $key => $val) {
            $execArray[":" . $key] = $val;
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
        $whereSql = null;

        if($id) {
            $id = htmlentities($id);
            $whereSql = "WHERE ".$this->table.".".$this->findPrimaryKey($this->table)." = '".$id."'";
        }

        if(isset($this->where)) {
            $whereSql = ($whereSql)?$whereSql." AND ".implode(" AND ",$this->where):"WHERE ".implode(" AND ",$this->where);
        }

        return $this->connection->exec("DELETE FROM ".$this->table." ".$whereSql);
    }

    public function find($id = null) {
        $whereSql = null;

        if($id) {
            $id = htmlentities($id);
            $whereSql = "WHERE ".$this->table.".".$this->findPrimaryKey($this->table)." = '".$id."'";
        }

        if(isset($this->where)) {
            $whereSql = ($whereSql)?$whereSql." AND ".implode(" AND ",$this->where):"WHERE ".implode(" AND ",$this->where);
        }

        $orderBySql = (isset($this->order_by))?"ORDER BY ".htmlentities($this->order_by):"";

        $joinSql = "";
        if($this->join) {
            foreach($this->join as $i=>$join) {
                $joinSql .= $this->join_type[$i]." ".$join." ";
            }
        }

        $this->last_query = "SELECT ".$this->select." FROM `".$this->table."` ".htmlentities($joinSql)." ".$whereSql." ".$orderBySql." LIMIT 1";
        $stmt = $this->connection->query($this->last_query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch();
    }

    public function all() {

        $join_sql = "";
        if($this->join) {
            foreach($this->join as $i=>$join) {
                $join_sql .= $this->join_type[$i]." ".$join." ";
            }
        }
        $where_sql = (isset($this->where))?"WHERE ".implode(" AND ",$this->where):"";
        $order_by_sql = (isset($this->order_by))?"ORDER BY ".htmlentities($this->order_by):"";
        $group_by_sql = (isset($this->group_by)) ? "GROUP BY ".htmlentities($this->group_by): "";
        $limit_sql = (isset($this->limit))?"LIMIT ".htmlentities($this->limit):"";
        $limit_sql .= (isset($this->offset))?" OFFSET ".htmlentities($this->offset):"";
        $this->last_query = "SELECT ".$this->select." FROM `".$this->table."` ".$join_sql." ".$where_sql." ".$group_by_sql." ".$order_by_sql." ".$limit_sql;
        $stmt = $this->connection->query($this->last_query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }


    public function paginate() {

        $page = request_int('page', 1);
        $this->offset = ($page - 1) * $this->limit;

        $join_sql = "";
        if($this->join) {
            foreach($this->join as $i=>$join) {
                $join_sql .= $this->join_type[$i]." ".$join." ";
            }
        }
        $where_sql = (isset($this->where))?"WHERE ".implode(" AND ",$this->where):"";
        $order_by_sql = (isset($this->order_by))?"ORDER BY ".htmlentities($this->order_by):"";
        $group_by_sql = (isset($this->group_by)) ? "GROUP BY ".htmlentities($this->group_by): "";
        $limit_sql = (isset($this->limit))?"LIMIT ".htmlentities($this->limit):"";
        $limit_sql .= (isset($this->offset))?" OFFSET ".htmlentities($this->offset):"";

        $this->last_query = "SELECT ".$this->select." FROM `".$this->table."` ".$join_sql." ".$where_sql." ".$group_by_sql." ".$order_by_sql." ".$limit_sql;
        $stmt = $this->connection->query($this->last_query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $data = [];
        $data['data'] = $stmt->fetchAll();

        // Get Total
        $stmt = $this->connection->query("SELECT COUNT(*) as total_records FROM `".$this->table."` ".$join_sql." ".$where_sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $data['total'] = $stmt->fetch()['total_records'];
        return $data;
    }

    public function getLastQuery()
    {
        return $this->last_query;
    }

    public function count() {
        $join_sql = "";

        if($this->join) {
            foreach($this->join as $i=>$join) {
                $join_sql .= $this->join_type[$i]." ".$join." ";
            }
        }

        $where_sql = (isset($this->where))?"WHERE ".implode(" AND ",$this->where):"";
        $this->last_query = "SELECT count(*) as total_records FROM `".$this->table."` ".htmlentities($join_sql)." ".$where_sql;
        $stmt = $this->connection->query($this->last_query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()['total_records'];
    }

    public function sum($field) {
        $join_sql = "";

        if($this->join) {
            foreach($this->join as $i=>$join) {
                $join_sql .= $this->join_type[$i]." ".$join." ";
            }
        }

        $where_sql = (isset($this->where))?"WHERE ".implode(" AND ",$this->where):"";
        $this->last_query = "SELECT sum($field) as total_records FROM ".$this->table." ".htmlentities($join_sql)." ".$where_sql;
        $stmt = $this->connection->query($this->last_query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->fetch()['total_records'];
    }

    public function listColumn($table) {
        $columns = [];
        $rs = $this->connection->query('SELECT * FROM '.$table.' LIMIT 0');
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
        return "RAND()";
    }

}