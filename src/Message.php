<?php

namespace JPI\HTTP;

class Message {

    protected $protocolVersion;
    protected $headers;
    protected $body;

    public function __construct(array $headers = [], string $body = "", float $protocolVersion = 1.1) {
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

    public function addHeader(string $header, $value): void {
        $this->headers->set($header, $value);
    }

    public function withHeader(string $header, $value): Message {
        $this->addHeader($header, $value);
        return $this;
    }

    public function hasHeader(string $name): bool {
        return $this->headers->isset($name);
    }

    public function getHeader(string $name): array {
        return $this->headers->get($name, []);
    }

    public function getHeaderLine(string $name): string {
        return implode(", ", $this->getHeader($name));
    }

    public function setBody(?string $body): void {
        $this->body = $body;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function withBody(?string $body): Message {
        $this->setBody($body);
        return $this;
    }
}
