<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;
use SuperFrameworkEngine\Helpers\BtCacheUpdater;

class PackageDiscover extends Command
{
    use OutputMessage;

    public function run() {
        $composers = $this->rglob(base_path("vendor")."/*/composer.json");
        if($composers) {
            foreach($composers as $composer) {
                $composerRaw = file_get_contents($composer);
                $composerJson = json_decode($composerRaw, true);
                if(isset($composerJson['extra']) && isset($composerJson['extra']['super-framework'])) {
                    $this->success("Discover package: " . $composerJson['name']);
                    $providers = $composerJson['extra']['super-framework']['providers'];
                    if($providers) {
                        foreach($providers as $provideClass) {
                            $reflectionClass = new $provideClass();
                            $files = [];
                            foreach($reflectionClass->commands() as $command) {
                                $files[] = [
                                    'path'=> (string) $command,
                                    'class'=> (string) $command
                                ];
                            }
                            BtCacheUpdater::updateCommand($files);

                            $files = [];
                            foreach($reflectionClass->boots() as $boot) {
                                $files[] = [
                                    'path'=> (string) $boot,
                                    'class'=> (string) $boot
                                ];
                            }
                            BtCacheUpdater::updateBoot($files);

                            $files = [];
                            foreach($reflectionClass->middlewares() as $middleware) {
                                $files[] = [
                                    'path'=> (string) $middleware,
                                    'class'=> (string) $middleware
                                ];
                            }
                            BtCacheUpdater::updateMiddleware($files);
                        }
                    }
                }
            }
        }
        $this->success("Package discover has been finished!");
    }

    private function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }
}