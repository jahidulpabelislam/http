<?php

declare(strict_types=1);

namespace JPI\HTTP;

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

    public function addRoute(string $path, string $method, callable|string $callback, string $name = null): void {
        $this->router->addRoute($path, $method, $callback, $name);
    }

    public function addMiddleware(RequestMiddlewareInterface $middleware): void {
        $this->middlewares[] = $middleware;
    }

    public function handle(): Response {
        if (!count($this->middlewares)) {
            return $this->router->handle();
        }

        $next = array_shift($this->middlewares);
        $next->setRequest($this->getRequest());
        return $next->run($this);
    }
}
