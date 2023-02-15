<?php

namespace JPI\HTTP;

class App implements RequestHandlerInterface {

    protected $request;
    protected $router;
    protected $middlewares;

    /**
     * @param Request $request
     * @param RequestMiddlewareInterface[] $middlewares
     */
    public function __construct(Request $request, Router $router, array $middlewares = []) {
        $this->request = $request;
        $this->router = $router;
        $this->middlewares = $middlewares;
    }

    public function setRequest(Request $request): void {
        $this->request = $request;
    }

    public function getRequest(): Request {
        return $this->request;
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
