<?php

namespace JPI\HTTP;

trait RequestAwareTrait {

    protected $request;

    public function setRequest(Request $request): void {
        $this->request = $request;
    }

    public function getRequest(): Request {
        return $this->request;
    }
}
