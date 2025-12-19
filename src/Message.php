<?php

declare(strict_types=1);

namespace JPI\HTTP;

/**
 * Base class for HTTP messages (Request and Response).
 *
 * Provides common functionality for managing headers, body content,
 * and protocol version shared by both requests and responses.
 */
class Message {

    protected Headers $headers;

    public function __construct(
        array $headers = [],
        protected string $body = "",
        protected float $protocolVersion = 1.1
    ) {
        $this->headers = new Headers($headers);
    }

    public function getProtocolVersion(): float {
        return $this->protocolVersion;
    }

    /**
     * Get all headers as a cloned collection.
     */
    public function getHeaders(): Headers {
        return clone $this->headers;
    }

    /**
     * Add a value to an existing header.
     *
     * If the header doesn't exist, it will be created with the value.
     */
    public function addHeader(string $header, $newValue): void {
        $value = $this->getHeader($header);
        $value[] = $newValue;
        $this->headers->set($header, $value);
    }

    public function setHeader(string $header, $value): void {
        $this->headers->set($header, $value);
    }

    /**
     * Set or add a header value (fluent interface).
     *
     * @param bool $add If true, adds to existing header; if false, replaces it
     */
    public function withHeader(string $header, $value, bool $add = false): Message {
        if ($add) {
            $this->addHeader($header, $value);
        }
        else {
            $this->setHeader($header, $value);
        }
        return $this;
    }

    public function removeHeader(string $header): void {
        $this->headers->unset($header);
    }

    public function hasHeader(string $name): bool {
        return $this->headers->isset($name);
    }

    public function getHeader(string $name): array {
        return $this->headers->get($name, []);
    }

    /**
     * Get a header value as a string.
     *
     * Multiple values are joined with commas.
     */
    public function getHeaderString(string $name): string {
        return implode(",", $this->getHeader($name));
    }

    public function setBody(string $body): void {
        $this->body = $body;
    }

    public function getBody(): string {
        return $this->body;
    }

    /**
     * Set the body content (fluent interface).
     */
    public function withBody(string $body): Message {
        $this->setBody($body);
        return $this;
    }
}
