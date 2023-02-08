<?php

namespace JPI\HTTP;

class Message {

    protected $protocolVersion;
    protected $headers;
    protected $body;

    public function __construct(float $protocolVersion, array $headers = [], string $body = '') {
        $this->protocolVersion = $protocolVersion;
        $this->headers = new Headers($headers);
        $this->body = $body;
    }

    public function getProtocolVersion(): float {
        return $this->protocolVersion;
    }

    public function getHeaders(): Headers {
        return $this->headers;
    }

    public function hasHeader(string $name): bool {
        return $this->headers->isset($name);
    }

    public function getHeader(string $name): array {
        return $this->headers->get($name, "");
    }

    public function getHeaderLine(string $name): string {
        return implode(", ", $this->headers->get($name, []));
    }

    public function getBody(): string {
        return $this->body;
    }
}
