<?php

namespace SuperFrameworkEngine\Commands;


use SuperFrameworkEngine\Foundation\Command;

class PackageDiscover extends Command
{
    use OutputMessage;

    public function run() {
        $composers = glob(base_path("vendor/{,*/,*/*/,*/*/*/}composer.json"));
        if($composers) {
            foreach($composers as $composer) {
                $composerRaw = file_get_contents($composer);
                $composerJson = json_decode($composerRaw, true);
                if(isset($composerJson['extra']) && isset($composerJson['extra']['super-framework'])) {
                    $providers = $composerJson['extra']['super-framework']['providers'];
                    if($providers) {
                        if(!file_exists(base_path("bootstrap".DIRECTORY_SEPARATOR."packages.php"))) {
                            $packages = [];
                        } else {
                            $packages = include base_path("bootstrap".DIRECTORY_SEPARATOR."packages.php");
                        }
                        $packages = array_merge($packages, $providers);
                        $packages = array_unique($packages);
                        file_put_contents(base_path("bootstrap".DIRECTORY_SEPARATOR."packages.php"), "<?php\n\nreturn ".var_min_export($packages, true).";");
                    }
                }
            }
        }
        $this->success("Package discover has been finished!");
    }
}