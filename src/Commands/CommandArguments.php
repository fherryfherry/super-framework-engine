<?php

namespace SuperFrameworkEngine\Commands;

trait CommandArguments
{
    /**
     * @param array $arguments
     * @param string $argument
     * @return mixed|string
     */
    public function getArgument(array $arguments, string $argument)
    {
        $result = [];
        foreach($arguments as $arg) {
            $argArr = explode("=",$arg);
            $key = ltrim(ltrim($argArr[0],"-"),"-");
            $val = isset($argArr[1]) ? $argArr[1] : true;
            if(is_string($val)) {
                $val = str_replace("--".$argument."=","",$val);
            }
            $result[$key] = $val;
        }
        if(isset($result[$argument])) {
            return $result[$argument];
        } else {
            return null;
        }
    }
}