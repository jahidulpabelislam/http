<?php

declare(strict_types=1);

namespace JPI\HTTP;

use OutOfBoundsException;
use JPI\Utils\URL;

/**
 * Handles route registration, matching, and execution.
 *
 * The router matches incoming requests against registered routes and executes
 * the appropriate callback or controller method. It supports route parameters,
 * named routes for URL generation, and automatic OPTIONS request handling.
 */
class Router implements RequestHandlerInterface {

    protected $notFoundHandler;
    protected $methodNotAllowedHandler;

    /** @var Route[] */
    protected array $routes = [];

    /** @var Route[] */
    protected array $namedRoutes = [];

    /**
     * @param callable $notFoundHandler Handler for 404 Not Found responses
     * @param callable $methodNotAllowedHandler Handler for 405 Method Not Allowed responses
     */
    public function __construct(
        protected Request $request,
        callable $notFoundHandler,
        callable $methodNotAllowedHandler
    ) {
        $this->notFoundHandler = $notFoundHandler;
        $this->methodNotAllowedHandler = $methodNotAllowedHandler;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    /**
     * Register a new route.
     *
     * @param string $pattern Route pattern with parameters in {param} format
     * @param string $method HTTP method (GET, POST, etc.)
     * @param callable|string $callback Closure or "ControllerClass::method" string
     * @param string|null $name Optional name for the route to enable URL generation
     */
    public function addRoute(string $pattern, string $method, callable|string $callback, ?string $name = null): void {
        $route = new Route($pattern, $method, $callback, $name);

        $this->routes[] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $route;
        }
    }

    /**
     * Generate a path for a named route.
     *
     * @throws OutOfBoundsException If the named route is not defined
     */
    public function getPathForRoute(string $name, array $params): string {
        if (!isset($this->namedRoutes[$name])) {
            throw new OutOfBoundsException("Named route $name not defined");
        }

        $path = $this->namedRoutes[$name]->getPattern();

        foreach ($params as $param => $value) {
            $path = str_replace("/{{$param}}/", "/$value/", $path);
        }

        return $path;
    }

    public function getURLForRoute(string $name, array $params): URL {
        return $this->getRequest()->makeURL(
            $this->getPathForRoute($name, $params)
        );
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

    /**
     * Handle the request by matching against registered routes.
     *
     * Automatically responds to OPTIONS requests with 200 OK.
     * Returns 405 if route matches but method doesn't, 404 if no route matches.
     */
    public function handle(): Response {
        $request = $this->getRequest();

        $path = $request->getURL()->getPath();

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
