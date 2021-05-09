<?php


namespace SuperFrameworkEngine\Foundation;


use FastRoute\RouteCollector;

trait ResponseBuilder
{
    /**
     * @return mixed
     * @throws \Exception
     */
    private function responseBuilder() {
        $dispatcher = \FastRoute\cachedDispatcher(function(RouteCollector $r) {
            foreach ($this->bootstrapCache['route'] as $pattern => $value) {
                if($pattern == "/" || $pattern == "") {
                    $route = "/";
                } else {
                    $route = trim($pattern,"/");
                }

                $route = base_path_uri($route);
                $route = "/" . trim($route,"/");
                if($route == "/") {
                    $r->addRoute(['GET','POST'], "",$value[0]."@".$value[1]);
                } else {
                    $r->addRoute(['GET','POST'], $route,$value[0]."@".$value[1]);
                }
            }
        },[
            'cacheFile' => base_path('bootstrap/route.cache')
        ]);

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $uri = rtrim($uri, "/");

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        $response = null;

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \Exception("The page is not found!", 404);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \Exception("The method is not allowed!", 405);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                list($class, $method) = explode("@", $handler, 2);
                $response = call_user_func_array([new $class, $method], $vars);
                break;
        }
        return $response;
    }

}