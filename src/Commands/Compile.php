<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;
use SuperFrameworkEngine\Helpers\BtCacheUpdater;
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
        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Command.php");
        BtCacheUpdater::updateCommand($files);

        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}*Command.php");
        BtCacheUpdater::updateCommand($files);
    }

    private function compileBoot($prefix_path = "app/") {
        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Boot.php");
        BtCacheUpdater::updateBoot($files);
    }

    private function compileMiddlewares($prefix_path = "app/") {
        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Middleware.php");
        BtCacheUpdater::updateMiddleware($files);

        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}*Middleware.php");
        BtCacheUpdater::updateMiddleware($files);
    }

    private function compileHelpers($prefix_path = "app/") {
        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}Configs".DIRECTORY_SEPARATOR."Helper.php");
        BtCacheUpdater::updateHelper($files);

        $files = BtCacheUpdater::findFiles($prefix_path."{,*/,*/*/,*/*/*/}*Helper.php");
        BtCacheUpdater::updateHelper($files);
    }
}