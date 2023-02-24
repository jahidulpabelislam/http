<?php

declare(strict_types=1);

namespace JPI\HTTP;

class Route {

    protected $pattern;
    protected $regex = null;

    protected $method;

    protected $callback;

    protected $name;

    public function __construct(string $pattern, string $method, $callback, string $name = null) {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->callback = $callback;
        $this->name = $name;
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
