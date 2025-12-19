<?php

declare(strict_types=1);

namespace JPI\HTTP;

/**
 * The main HTTP application container that manages routing and middleware processing.
 *
 * This class coordinates the request handling flow by executing middleware in sequence
 * before delegating to the router for route matching and execution.
 */
class App implements RequestHandlerInterface {

    /**
     * @param RequestMiddlewareInterface[] $middlewares
     */
    public function __construct(
        protected Router $router,
        protected array $middlewares = []
    ) {
    }

    public function getRequest(): Request {
        return $this->router->getRequest();
    }

    /**
     * Register a new route with the router.
     */
    public function addRoute(string $path, string $method, callable|string $callback, ?string $name = null): void {
        $this->router->addRoute($path, $method, $callback, $name);
    }

    /**
     * Add middleware to the application's middleware stack.
     *
     * Middleware are executed in the order they are added.
     */
    public function addMiddleware(RequestMiddlewareInterface $middleware): void {
        $this->middlewares[] = $middleware;
    }

    /**
     * Handle the incoming request by processing middleware and routing.
     *
     * If middleware are registered, they are executed in sequence with each
     * middleware having the opportunity to modify the request or short-circuit
     * processing. Otherwise, the request is passed directly to the router.
     */
    public function handle(): Response {
        if (!count($this->middlewares)) {
            return $this->router->handle();
        }

        $next = array_shift($this->middlewares);
        $next->setRequest($this->getRequest());
        return $next->run($this);
    }
}
