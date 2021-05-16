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
            $val = $argArr[1];
            $result[$key] = $val;
        }
        if(isset($result[$argument])) {
            return $result[$argument];
        } else {
            throw new \InvalidArgumentException("There is no argument : {$argument}");
        }
    }
}