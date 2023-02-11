<?php

namespace JPI\HTTP;

interface RequestHandlerInterface {

    public function run(): Response;
}
