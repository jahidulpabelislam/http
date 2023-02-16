<?php

declare(strict_types=1);

namespace JPI\HTTP;

interface RequestMiddlewareInterface {

    public function setRequest(Request $request): void;

    public function getRequest(): Request;

    public function run(RequestHandlerInterface $next): Response;
}
