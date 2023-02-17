<?php

declare(strict_types=1);

namespace JPI\HTTP;

interface RequestHandlerInterface {

    public function handle(): Response;
}
