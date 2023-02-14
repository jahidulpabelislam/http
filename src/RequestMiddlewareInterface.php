<?php

namespace JPI\HTTP;

interface RequestMiddlewareInterface {

    public function setRequest(Request $request): void;

    public function getRequest(): Request;

    public function run(RequestMiddlewareInterface $next): Response;
}
