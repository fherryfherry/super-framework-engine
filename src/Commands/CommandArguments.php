<?php


namespace SuperFrameworkEngine\Commands;


trait CommandArguments
{
    public function getArgument(array $arguments, string $key)
    {
        $result = [];
        foreach($arguments as $arg) {
            $argArr = explode("=",$arg);
            $key = ltrim(ltrim($argArr[0],"-"),"-");
            $val = $argArr[1];
            $result[$key] = $val;
        }
        return $result[$key];
    }
}