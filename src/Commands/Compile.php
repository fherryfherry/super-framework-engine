<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;
use SuperFrameworkEngine\Helpers\RouteParser;

class Compile extends Command
{
    use OutputMessage;

    /**
     * @throws \ReflectionException
     */
    public function run() {
        RouteParser::generateRoute();

        // System App Compiling
        $this->compileBoot(realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR));
        $this->compileMiddlewares(realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR));
        $this->compileHelpers(realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR));
        $this->compileCommand(realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR));

        // User App Compiling
        $this->compileBoot(base_path("app".DIRECTORY_SEPARATOR));
        $this->compileMiddlewares(base_path("app".DIRECTORY_SEPARATOR));
        $this->compileHelpers(base_path("app".DIRECTORY_SEPARATOR));
        $this->compileCommand(base_path("app".DIRECTORY_SEPARATOR));

        $this->success("Application has been recompiled!");
    }

    private function compileCommand($prefix_path = "app/") {
        $files = glob($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Command.php", GLOB_BRACE);
        foreach($files as $i=>$file) {
            $classPath = RouteParser::cleanClassName($file);
            $files[$i] = ['path'=>$classPath,'class'=>str_replace("vendor\\fherryfherry\\super-framework-engine\\src","SuperFrameworkEngine", $classPath)];
        }
        $files = array_values($files);
        $boots = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $boots['command'] = array_merge($boots['command']?:[],$files);
        $boots['command'] = $this->arrayUnique($boots['command'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($boots, true).";");
    }

    private function compileBoot($prefix_path = "app/") {
        $files = glob($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Boot.php", GLOB_BRACE);
        foreach($files as $i=>$file) {
            $classPath = RouteParser::cleanClassName($file);
            $files[$i] = ['path'=>$classPath,'class'=>str_replace("vendor\\fherryfherry\\super-framework-engine\\src","SuperFrameworkEngine", $classPath)];
        }
        $files = array_values($files);
        $boots = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $boots['boot'] = ($boots['boot']) ?: [];
        $boots['boot'] = array_merge($boots['boot'],$files);
        $boots['boot'] = $this->arrayUnique($boots['boot'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($boots, true).";");
    }

    private function compileMiddlewares($prefix_path = "app/") {
        $files = glob($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Middleware.php", GLOB_BRACE);
        foreach($files as $i=>$file) {
            $classPath = RouteParser::cleanClassName($file);
            $files[$i] = ['path'=>$classPath,'class'=>str_replace("vendor\\fherryfherry\\super-framework-engine\\src","SuperFrameworkEngine", $classPath)];
        }
        $files = array_values($files);
        $boots = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $boots['middleware'] = ($boots['middleware']) ?: [];
        $boots['middleware'] = array_merge($boots['middleware'],$files);
        $boots['middleware'] = $this->arrayUnique($boots['middleware'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($boots, true).";");
    }

    private function compileHelpers($prefix_path = "app/") {
        $files = glob($prefix_path."{,*/,*/*/,*/*/*/}Configs/Helper.php", GLOB_BRACE);
        foreach($files as $i=>$file) {
            $classPath = RouteParser::cleanClassName($file);
            $files[$i] = ['path'=>$classPath,'class'=>str_replace("vendor\\fherryfherry\\super-framework-engine\\src","SuperFrameworkEngine", $classPath)];
        }
        $files = array_values($files);
        $boots = include base_path("bootstrap".DIRECTORY_SEPARATOR."cache.php");
        $boots['helper'] = ($boots['helper']) ?: [];
        $boots['helper'] = array_merge($boots['helper']?:[],$files);
        $boots['helper'] = $this->arrayUnique($boots['helper'], "path");
        file_put_contents(base_path('bootstrap'.DIRECTORY_SEPARATOR.'cache.php'), "<?php\n\nreturn ".var_min_export($boots, true).";");
    }

    private function arrayUnique(array $array, string $key) {
        $temp = array_unique(array_column($array, $key));
        $unique_arr = array_intersect_key($array, $temp);
        return $unique_arr;
    }
}