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

        if($search = request('search')['value']) {
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

        if(request('order')) {
            $order_column_idx = (int) request('order')[0]['column'];
            $order_column = request('columns')[$order_column_idx]['data'];
            $order_column_dir = request('order')[0]['dir'];
            
            // Security: Whitelist order direction and sanitize column name
            $order_column_dir = in_array(strtolower($order_column_dir), ['asc', 'desc']) ? $order_column_dir : 'desc';
            $order_column = preg_replace('/[^a-zA-Z0-9_\.]/', '', $order_column);
            
            if($order_column) {
                $result->orderBy($order_column." ".$order_column_dir);
            }
        } else {
            $order_column = $this->table.".".db()->findPrimaryKey($this->table);
            $order_column_dir = "desc";
            $result->orderBy($order_column." ".$order_column_dir);
        }
        $result->offset(request_int('start'));
        $result->limit(request_int('length'));
        $data = $result->all();

        $no_start = request_int('start');
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

        if($search = request('search')['value']) {
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

        $records_total_filtered = $result->count();

        return [
            'draw'=> request_int('draw'),
            'recordsTotal'=> $records_total,
            'recordsFiltered'=> $records_total_filtered,
            'data'=> $data
        ];
    }

}