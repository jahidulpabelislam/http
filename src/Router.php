<?php

declare(strict_types=1);

namespace JPI\HTTP;

use Exception;
use JPI\Utils\URL;

class Router implements RequestHandlerInterface {

    protected $notFoundHandler;
    protected $methodNotAllowedHandler;

    protected $routes = [];
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
     * @param $path string
     * @param $method string
     * @param $callback callable|string
     * @param $name string|null
     */
    public function addRoute(string $path, string $method, $callback, string $name = null): void {
        if (!isset($this->routes[$path])) {
            $this->routes[$path] = [];
        }

        $this->routes[$path][$method] = [
            "callback" => $callback,
        ];

        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    public function makePath(string $name, array $params): string {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Named route $name not defined");
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $identifier => $value) {
            $path = str_replace("/{{$identifier}}/", "/$value/", $path);
        }

        return $path;
    }

    public function makeURL(string $name, array $params): URL {
        $path = $this->makePath($name, $params);

        $url = $this->getRequest()->getURL();
        $url->setPath($path);
        return $url;
    }

    protected function getIdentifiersFromMatches(array $matches): array {
        $identifiers = [];

        foreach ($matches as $key => $match) {
            if (!is_numeric($key)) {
                $identifiers[$key] = $match;
            }
        }

        return $identifiers;
    }

    protected function pathToRegex(string $path): string {
        $regex = preg_replace("/\/{([A-Za-z]*?)}\//", "/(?<$1>[^/]*)/", $path);
        $regex = str_replace("/", "\/", $regex);
        return "/^{$regex}$/";
    }

    public function handle(): Response {
        $request = $this->request;

        $url = $request->getURL();
        $uri = $url->getPath();

        $method = $request->getMethod();

        $routeMatchedNotMethod = false;

        foreach ($this->routes as $path => $routes) {
            $pathRegex = $this->pathToRegex($path);
            if (!preg_match($pathRegex, $uri, $matches)) {
                continue;
            }

            if ($method === "OPTIONS") {
                return new Response(200);
            }

            if (!isset($routes[$method])) {
                $routeMatchedNotMethod = true;
                continue;
            }

            array_shift($matches);
            $identifiers = $this->getIdentifiersFromMatches($matches);

            $request->setAttribute("identifiers", $identifiers);

            $route = $routes[$method];

            $identifiers = array_values($identifiers);

            if (is_callable($route["callback"])) {
                return $route["callback"]($request, ...$identifiers);
            }

            $callbackParts = explode("::", $route["callback"]);

            $controllerClass = $callbackParts[0];
            $controller = new $controllerClass();

            return $controller->{$callbackParts[1]}($request, ...$identifiers);
        }

        if ($routeMatchedNotMethod) {
            return call_user_func($this->methodNotAllowedHandler, $request);
        }

        return call_user_func($this->notFoundHandler, $request);
    }
}
