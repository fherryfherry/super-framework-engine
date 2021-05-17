<?php


namespace SuperFrameworkEngine\Helpers;


class Collection
{
    private $dataArray;
    private $result = null;

    public function __construct(array $array) {
        $this->dataArray = $array;
    }

    /**
     * @param string|null $key
     * @return array|mixed|string|null
     */
    public function first(string $key = null)
    {
        $data = (is_array($this->result)) ? $this->result : $this->dataArray;
        $first = array_slice($data,0, 1);
        return ($key) ? $first[$key] : $first;
    }

    /**
     * @return int
     */
    public function count()
    {
        $data = (is_array($this->result)) ?  $this->result : $this->dataArray;
        return count($data);
    }

    /**
     * @return bool
     */
    public function exist()
    {
        return $this->count() > 0;
    }

    /**
     * @return array
     */
    public function get()
    {
        return (is_array($this->result)) ? $this->result : $this->dataArray;
    }

    public function sum(string $key)
    {
        $data = (is_array($this->result)) ? $this->result : $this->dataArray;
        $total = 0;
        foreach($data as $row) {
            $total += $row[$key];
        }
        return $total;
    }

    public function average(string $key)
    {
        $data = (is_array($this->result)) ? $this->result : $this->dataArray;
        $total = 0;
        foreach($data as $row) {
            $total += $row[$key];
        }
        return $total / count($data);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereEqual(string $key, string $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && $row[$key] == $value) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereNotEqual(string $key, string $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && $row[$key] != $value) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param array $value
     * @return $this
     */
    public function whereIn(string $key, array $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && in_array($row[$key], $value)) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param array $value
     * @return $this
     */
    public function whereNotIn(string $key, array $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && !in_array($row[$key], $value)) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereLike(string $key, string $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && stripos($row[$key], $value) !== false) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function whereNotLike(string $key, string $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && stripos($row[$key], $value) === false) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function whereGreaterThan(string $key, $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && $row[$key] > $value) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function whereLessThan(string $key, $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && $row[$key] < $value) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function whereGreaterThanEq(string $key, $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && $row[$key] >= $value) {
                $this->result[] = $row;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function whereLessThanEq(string $key, $value)
    {
        if(is_array($this->result)) {
            $data = $this->result;
        } else {
            $data = $this->dataArray;
            $this->result = [];
        }
        foreach($data as $row) {
            if(isset($row[$key]) && $row[$key] <= $value) {
                $this->result[] = $row;
            }
        }
        return $this;
    }
}