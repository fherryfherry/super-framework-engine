<?php


namespace SuperFrameworkEngine\Foundation;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;

trait ResponseBuilder
{
    /**
     * @return mixed
     * @throws Exception
     */
    private function responseBuilder(): mixed
    {
        $dispatcher = cachedDispatcher(function (RouteCollector $r) {
            foreach ($this->bootstrapCache['route'] as $pattern => $value) {
                if ($pattern === "/" || $pattern === "") {
                    $route = "/";
                } else {
                    $route = trim($pattern, "/");
                }

                $route = base_path_uri($route);
                $route = "/" . trim($route, "/");
                if ($route === "/") {
                    $r->addRoute(['GET', 'POST'], "", $value[0] . "@" . $value[1]);
                } else {
                    $r->addRoute(['GET', 'POST'], $route, $value[0] . "@" . $value[1]);
                }
            }
        }, [
            'cacheFile' => base_path('bootstrap/route.cache')
        ]);

        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, (int) $pos);
        }
        $uri = rawurldecode($uri);
        $uri = rtrim($uri, "/");

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND => throw new Exception("The page is not found!", 404),
            Dispatcher::METHOD_NOT_ALLOWED => throw new Exception("The method is not allowed!", 405),
            Dispatcher::FOUND => $this->handleFoundRoute($routeInfo[1], $routeInfo[2]),
            default => throw new Exception("Internal Server Error", 500),
        };
    }

    /**
     * @param string $handler
     * @param array<string, mixed> $vars
     * @return mixed
     */
    private function handleFoundRoute(string $handler, array $vars): mixed
    {
        [$class, $method] = explode("@", $handler, 2);
        $instance = Container::getInstance()->make($class);
        return call_user_func_array([$instance, $method], $vars);
    }
}