<?php

declare(strict_types=1);

namespace JPI\HTTP;

/**
 * Trait providing request storage and access.
 *
 * Useful for controllers and middleware that need access to the current request.
 */
trait RequestAwareTrait {

    protected Request $request;

    public function setRequest(Request $request): void {
        $this->request = $request;
    }

    public function getRequest(): Request {
        return $this->request;
    }
}
