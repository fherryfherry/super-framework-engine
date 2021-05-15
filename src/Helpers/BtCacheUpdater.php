<?php


namespace SuperFrameworkEngine\Helpers;


class BtCacheUpdater
{
    public static function findFiles(string $path)
    {
        $files = glob($path, GLOB_BRACE);
        foreach($files as $i=>$file) {
            $classPath = RouteParser::cleanClassName($file);
            $files[$i] = ['path'=>$classPath,'class'=>str_replace("vendor\\fherryfherry\\super-framework-engine\\src","SuperFrameworkEngine", $classPath)];
        }
        return $files;
    }

    public static function updateCommand(array $newCommandArray)
    {
        $newCommandArray = array_values($newCommandArray);
        $btArray = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $btArray['command'] = array_merge($btArray['command']?:[],$newCommandArray);
        $btArray['command'] = array_unique_multi($btArray['command'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($btArray, true).";");
    }

    public static function updateBoot(array $newCommandArray)
    {
        $newCommandArray = array_values($newCommandArray);
        $btArray = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $btArray['boot'] = array_merge($btArray['boot']?:[],$newCommandArray);
        $btArray['boot'] = array_unique_multi($btArray['boot'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($btArray, true).";");
    }

    public static function updateMiddleware(array $newCommandArray)
    {
        $newCommandArray = array_values($newCommandArray);
        $btArray = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $btArray['middleware'] = array_merge($btArray['middleware']?:[],$newCommandArray);
        $btArray['middleware'] = array_unique_multi($btArray['middleware'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($btArray, true).";");
    }

    public static function updateHelper(array $newCommandArray)
    {
        $newCommandArray = array_values($newCommandArray);
        $btArray = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $btArray['helper'] = array_merge($btArray['helper']?:[],$newCommandArray);
        $btArray['helper'] = array_unique_multi($btArray['helper'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($btArray, true).";");
    }
}