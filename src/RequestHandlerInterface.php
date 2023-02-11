<?php

namespace JPI\HTTP;

interface RequestHandlerInterface {

    public function run(Request $request, ?RequestHandlerInterface $next = null): Response;
}
