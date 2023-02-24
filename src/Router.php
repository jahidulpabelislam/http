<?php

declare(strict_types=1);

namespace JPI\HTTP;

use Exception;
use JPI\Utils\URL;

class Router implements RequestHandlerInterface {

    protected $notFoundHandler;
    protected $methodNotAllowedHandler;

    /**
     * @var Route[]
     */
    protected $routes = [];

    /**
     * @var Route[]
     */
    protected $namedRoutes = [];

    public function __construct(
        Request $request,
        callable $notFoundHandler,
        callable $methodNotAllowedHandler
    ) {
        $this->request = $request;
        $this->notFoundHandler = $notFoundHandler;
        $this->methodNotAllowedHandler = $methodNotAllowedHandler;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    /**
     * @param $pattern string
     * @param $method string
     * @param $callback callable|string
     * @param $name string|null
     */
    public function addRoute(string $pattern, string $method, $callback, string $name = null): void {
        $route = new Route($pattern, $method, $callback, $name);

        $this->routes[] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }
    }

    public function makePath(string $name, array $params): string {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Named route $name not defined");
        }

        $path = $this->namedRoutes[$name]->getPattern();

        foreach ($params as $param => $value) {
            $path = str_replace("/{{$param}}/", "/$value/", $path);
        }

        return $path;
    }

    public function makeURL(string $name, array $params): URL {
        $path = $this->makePath($name, $params);

        $url = $this->getRequest()->getURL();
        $url->setPath($path);
        return $url;
    }

    protected function getRouteParamsFromMatches(array $matches): array {
        $params = [];

        foreach ($matches as $key => $match) {
            if (!is_numeric($key)) {
                $params[$key] = $match;
            }
        }

        return $params;
    }

    public function handle(): Response {
        $request = $this->request;

        $url = $request->getURL();
        $path = $url->getPath();

        $requestMethod = $request->getMethod();

        $routeMatchedNotMethod = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route->getRegex(), $path, $matches)) {
                continue;
            }

            if ($requestMethod === "OPTIONS") {
                return new Response(200);
            }

            if ($route->getMethod() !== $requestMethod) {
                $routeMatchedNotMethod = true;
                continue;
            }

            array_shift($matches);
            $routeParams = $this->getRouteParamsFromMatches($matches);

            $request->setAttribute("route_params", $routeParams);

            $routeParams = array_values($routeParams);

            $callback = $route->getCallback();
            if (is_callable($callback)) {
                return $callback($request, ...$routeParams);
            }

            $callbackParts = explode("::", $callback);

            $controllerClass = $callbackParts[0];
            $controller = new $controllerClass();
            $controller->setRequest($request);

            return $controller->{$callbackParts[1]}(...$routeParams);
        }

        if ($routeMatchedNotMethod) {
            return call_user_func($this->methodNotAllowedHandler, $request);
        }

        return call_user_func($this->notFoundHandler, $request);
    }
}
