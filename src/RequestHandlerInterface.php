<?php

declare(strict_types=1);

namespace JPI\HTTP;

interface RequestHandlerInterface {

    public function setRequest(Request $request): void;

    public function getRequest(): Request;

    public function handle(): Response;
}
