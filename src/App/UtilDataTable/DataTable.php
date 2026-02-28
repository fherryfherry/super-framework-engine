<?php

namespace SuperFrameworkEngine\App\UtilDataTable;

use SuperFrameworkEngine\App\UtilORM\ORM;

class DataTable
{
    private $table;
    private $searchable_columns;
    private $query;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function query($query) {
        $this->query = $query;
    }

    public function searchable(array $columns)
    {
        $this->searchable_columns = $columns;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function get() {
        $result = db($this->table);

        if(isset($this->query)) {
            $result = call_user_func($this->query, $result);
        }

        if($search_request = request('search')) {
            if($search = $search_request['value'] ?? null) {
                if($this->searchable_columns) {
                    $likes = [];
                    $binds = [];
                    foreach($this->searchable_columns as $column) {
                        $likes[] = $column." LIKE ?";
                        $binds[] = "%".$search."%";
                    }
                    $result->where("(".implode(" OR ",$likes).")", $binds);
                }
            }
        }

        if($order_request = request('order')) {
            $order_column_idx = (int) $order_request[0]['column'];
            $order_column = request('columns')[$order_column_idx]['data'] ?? null;
            $order_column_dir = $order_request[0]['dir'] ?? 'desc';
            
            // Security: Whitelist order direction and sanitize column name
            $order_column_dir = in_array(strtolower($order_column_dir), ['asc', 'desc']) ? $order_column_dir : 'desc';
            $order_column = $order_column ? preg_replace('/[^a-zA-Z0-9_\.]/', '', $order_column) : null;
            
            if($order_column) {
                $result->orderBy($order_column." ".$order_column_dir);
            }
        } else {
            $order_column = $this->table.".".db()->findPrimaryKey($this->table);
            $order_column_dir = "desc";
            $result->orderBy($order_column." ".$order_column_dir);
        }
        $result->offset(request_int('start', 0));
        $result->limit(request_int('length', 10));
        $data = $result->all();

        $no_start = request_int('start', 0);
        foreach($data as &$item) {
            $no_start++;
            $item['_number'] = $no_start;
        }

        $result = db($this->table);
        if(isset($this->query)) {
            $result = call_user_func($this->query, $result);
        }
        $records_total = $result->count();

        $result = db($this->table);
        if(isset($this->query)) {
            $result = call_user_func($this->query, $result);
        }

        if($search_request = request('search')) {
            if($search = $search_request['value'] ?? null) {
                if($this->searchable_columns) {
                    $likes = [];
                    $binds = [];
                    foreach($this->searchable_columns as $column) {
                        $likes[] = $column." LIKE ?";
                        $binds[] = "%".$search."%";
                    }
                    $result->where("(".implode(" OR ",$likes).")", $binds);
                }
            }
        }

        $records_total_filtered = $result->count();

        return [
            'draw'=> request_int('draw', 0),
            'recordsTotal'=> $records_total,
            'recordsFiltered'=> $records_total_filtered,
            'data'=> $data
        ];
    }

}