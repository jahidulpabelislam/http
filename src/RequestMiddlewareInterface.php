<?php

declare(strict_types=1);

namespace JPI\HTTP;

/**
 * Interface for HTTP middleware.
 *
 * Middleware can intercept requests before they reach route handlers,
 * allowing for tasks like authentication, logging, or request modification.
 */
interface RequestMiddlewareInterface {

    public function setRequest(Request $request): void;

    public function getRequest(): Request;

    /**
     * Execute middleware logic.
     *
     * Can return a response directly or call $next->handle() to continue
     * processing through the middleware chain and route handler.
     */
    public function run(RequestHandlerInterface $next): Response;
}
