<?php

declare(strict_types=1);

namespace JPI\HTTP;

/**
 * Interface for request handlers.
 *
 * Implemented by classes that can process HTTP requests and return responses,
 * such as App and Router.
 */
interface RequestHandlerInterface {

    public function handle(): Response;
}
