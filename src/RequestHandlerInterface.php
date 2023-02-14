<?php

namespace JPI\HTTP;

interface RequestHandlerInterface {

    public function setRequest(Request $request): void;

    public function getRequest(): Request;

    public function handle(): Response;
}
