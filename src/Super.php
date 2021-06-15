<?php

namespace SuperFrameworkEngine;

use Dotenv\Dotenv;
use SuperFrameworkEngine\Foundation\ResponseBuilder;

class Super
{
    use ResponseBuilder;

    private $config;
    private $bootstrapCache;

    public function __construct()
    {
        /**
         * Disable display error because we want to replace it with our display error page
         */
        ini_set("display_errors", 0);
        ini_set("display_startup_errors", 0);
        ini_set("error_log", base_path("error.log"));

        /**
         * Activate ENV functionality
         */
        Dotenv::createImmutable(base_path())->load();


        /**
         * Load configuration and bootstrap cache
         */
        $this->config = include base_path("configs/App.php");
        $this->bootstrapCache = include base_path("bootstrap/cache.php");

        /**
         * Set default timezone
         */
        date_default_timezone_set($this->config["timezone"] ?: "UTC");
    }

    private function loadHelpers() {
        foreach($this->bootstrapCache['helper'] as $helper) require_once base_path(lcfirst(str_replace("\\",DIRECTORY_SEPARATOR,$helper['path'])).".php");
    }

    private function middleware(callable $content) {
        $response = $content;
        $middleware = $this->bootstrapCache['middleware'];
        if(count($middleware)) {
            foreach($middleware as $mid) {
                $response = (new $mid['class'])->handle(function() use ($response) {
                    return $response;
                });
            }
        }
        return call_user_func($response);
    }

    private function boot() {
        $boot = $this->bootstrapCache['boot'];
        if(count($boot)) {
            foreach($boot as $b) {
                (new $b['class'])->run();
            }
        }
    }

    public function run() {
        try {
            $response = null;

            $this->loadHelpers();

            $this->boot();

            $response = $this->middleware(function () {
                return $this->responseBuilder();
            });

            echo $response;

        } catch (\Throwable $e) {
            http_response_code($e->getCode()?:500);

            if($this->config['logging_errors'] == "true") {
                logging($e);
            }

            if($this->config['display_errors'] == "true") {
                echo $e;
            } else {
                $blade = new \Jenssegers\Blade\Blade(__DIR__."/Views",base_path("bootstrap/views"));
                switch ($e->getCode()) {
                    default:
                    case "500":
                        echo $blade->make("error.500")->render();
                        break;
                    case "404":
                        echo $blade->make("error.404")->render();
                        break;
                    case "405":
                        echo $blade->make("error.405")->render();
                        break;
                }
            }
        }
    }

}