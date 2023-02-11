<?php

namespace JPI\HTTP;

interface RequestMiddlewareInterface extends RequestHandlerInterface {

    public function run(Request $request, ?RequestHandlerInterface $next = null): Response;
}
