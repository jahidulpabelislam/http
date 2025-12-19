<?php

declare(strict_types=1);

namespace JPI\HTTP;

use DateTime;
use DateTimeZone;

/**
 * Represents an HTTP response.
 *
 * Provides methods to set status codes, headers, and body content.
 * Supports JSON responses, cache headers, and ETag generation.
 */
class Response extends Message {

    protected ?string $statusMessage = null;

    public function __construct(
        protected int $statusCode = 500,
        protected string $body = "",
        array $headers = [],
        protected float $protocolVersion = 1.1
    ) {
        $this->headers = new Headers($headers);
    }

    /**
     * Create a JSON response.
     *
     * Sets the Content-Type header to application/json and encodes the body as JSON.
     */
    public static function json(
        int $statusCode = 500,
        array $body = [],
        array $headers = [],
        float $protocolVersion = 1.1
    ): Response {
        $response = new static($statusCode, "", $headers, $protocolVersion);
        $response->withJSON($body);
        return $response;
    }

    /**
     * Set cache-related headers.
     *
     * Automatically formats DateTime Expires headers and generates ETags when requested.
     *
     * @param array $headers Array of cache headers (Cache-Control, Expires, ETag, etc.)
     */
    public function setCacheHeaders(array $headers): void {
        if (isset($headers["Expires"]) && $headers["Expires"] instanceof DateTime) {
            $headers["Expires"]->setTimezone(new DateTimeZone("Europe/London"));
            $headers["Expires"] = $headers["Expires"]->format("D, d M Y H:i:s") . " GMT";
        }

        if (isset($headers["ETag"]) && $headers["ETag"]) {
            $headers["ETag"] = $this->getETag();
        }

        foreach ($headers as $header => $value) {
            $this->headers->set($header, $value);
        }
    }

    /**
     * Set cache headers (fluent interface).
     */
    public function withCacheHeaders(array $headers): Response {
        $this->setCacheHeaders($headers);
        return $this;
    }

    /**
     * Set the HTTP status code and optional message.
     */
    public function setStatus(int $code, ?string $message = null): void {
        $this->statusCode = $code;
        $this->statusMessage = $message;
    }

    /**
     * Set the status code and message (fluent interface).
     */
    public function withStatus(int $code, ?string $message = null): Response {
        $this->setStatus($code, $message);
        return $this;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * Get the status message for the current status code.
     *
     * If no custom message was set, returns the standard HTTP status message.
     */
    public function getStatusMessage(): string {
        if (is_null($this->statusMessage)) {
            $this->statusMessage = Status::MESSAGES[$this->getStatusCode()];
        }

        return $this->statusMessage;
    }

    /**
     * Set the body as JSON and set appropriate Content-Type header (fluent interface).
     */
    public function withJSON(array $body): Response {
        $this->body = json_encode($body);
        $this->setHeader("Content-Type", "application/json");
        return $this;
    }

    /**
     * Generate an ETag from the response body using MD5 hash.
     */
    public function getETag(): string {
        return md5($this->getBody());
    }

    /**
     * Send all response headers to the client.
     */
    protected function sendHeaders(): void {
        if (!is_null($this->body)) {
            foreach ($this->headers as $name => $value) {
                if (is_array($value)) {
                    $value = implode(", ", $value);
                }

                header("$name: $value");
            }
        }

        header("HTTP/{$this->getProtocolVersion()} {$this->getStatusCode()} {$this->getStatusMessage()}");
    }

    /**
     * Send the complete response (headers and body) to the client.
     */
    public function send(): void {
        $this->sendHeaders();

        echo $this->getBody();
    }
}
