<?php

namespace SuperFrameworkEngine;

use Dotenv\Dotenv;
use Jenssegers\Blade\Blade;
use SuperFrameworkEngine\Foundation\Container;
use SuperFrameworkEngine\Foundation\ResponseBuilder;
use Throwable;

class Super
{
    use ResponseBuilder;

    private array $config;
    private array $bootstrapCache;
    private Container $container;

    public function __construct()
    {
        /**
         * Disable display error because we want to replace it with our display error page
         */
        ini_set("display_errors", "0");
        ini_set("display_startup_errors", "0");
        ini_set("error_log", (string) base_path("error.log"));

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

        $this->container = Container::getInstance();
        $this->registerCoreBindings();
    }

    private function registerCoreBindings(): void
    {
        $this->container->singleton(Super::class, $this);
        $this->container->singleton('config', fn() => $this->config);
    }

    private function loadHelpers(): void
    {
        foreach ($this->bootstrapCache['helper'] as $helper) {
            require_once base_path(lcfirst(str_replace("\\", DIRECTORY_SEPARATOR, $helper['path'])) . ".php");
        }
    }

    private function middleware(callable $content): mixed
    {
        $response = $content;
        $middleware = $this->bootstrapCache['middleware'];

        if (count($middleware) > 0) {
            foreach ($middleware as $mid) {
                $instance = $this->container->make($mid['class']);
                $response = fn() => $instance->handle(fn() => $response());
            }
        }

        return $response();
    }

    private function boot(): void
    {
        $boot = $this->bootstrapCache['boot'];
        if (count($boot) > 0) {
            foreach ($boot as $b) {
                $instance = $this->container->make($b['class']);
                $instance->run();
            }
        }
    }

    private function responseCode(int $code): int
    {
        return in_array($code, [200, 400, 401, 404, 403, 500]) ? $code : 500;
    }

    public function run(): void
    {
        try {
            $this->loadHelpers();
            $this->boot();

            $response = $this->middleware(fn() => $this->responseBuilder());

            echo $response;
        } catch (Throwable $e) {
            $code = $this->responseCode((int) $e->getCode());
            http_response_code($code);

            if (($this->config['logging_errors'] ?? 'false') === "true") {
                logging($e);
            }

            if (($this->config['display_errors'] ?? 'false') === "true") {
                echo $e;
            } else {
                $this->renderErrorPage($e);
            }
        }
    }

    private function renderErrorPage(Throwable $e): void
    {
        $blade = new Blade(__DIR__ . "/Views", base_path("bootstrap/views"));
        $view = match ((string) $e->getCode()) {
            "404" => "error.404",
            "405" => "error.405",
            default => "error.500",
        };
        echo $blade->make($view)->render();
    }
}