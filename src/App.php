<?php

declare(strict_types=1);

namespace JPI\HTTP;

class App implements RequestHandlerInterface {

    use RequestAwareTrait;

    protected $router;
    protected $middlewares;

    /**
     * @param Request $request
     * @param Router $router
     * @param RequestMiddlewareInterface[] $middlewares
     */
    public function __construct(Request $request, Router $router, array $middlewares = []) {
        $this->request = $request;
        $this->router = $router;
        $this->middlewares = $middlewares;
    }

    public function handle(): Response {
        if (!count($this->middlewares)) {
            $this->router->setRequest($this->getRequest());
            return $this->router->handle();
        }

        $next = array_shift($this->middlewares);
        $next->setRequest($this->getRequest());
        return $next->run($this);
    }
}
