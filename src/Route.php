<?php

declare(strict_types=1);

namespace JPI\HTTP;

class Route {

    protected ?string $regex = null;

    public function __construct(
        protected string $pattern,
        protected string $method,
        protected $callback,
        protected ?string $name = null
    ) {
    }

    public function getPattern(): string {
        return $this->pattern;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getCallback() {
        return $this->callback;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getRegex(): string {
        if ($this->regex === null) {
            $regex = preg_replace("/\/{([A-Za-z]*?)}\//", "/(?<$1>[^/]*)/", $this->pattern);
            $regex = str_replace("/", "\/", $regex);
            $this->regex = "/^{$regex}$/";
        }

        return $this->regex;
    }
}
