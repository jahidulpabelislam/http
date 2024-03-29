<?php

declare(strict_types=1);

namespace JPI\HTTP;

use DateTime;
use DateTimeZone;

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

    public function withCacheHeaders(array $headers): Response {
        $this->setCacheHeaders($headers);
        return $this;
    }

    public function setStatus(int $code, ?string $message = null): void {
        $this->statusCode = $code;
        $this->statusMessage = $message;
    }

    public function withStatus(int $code, ?string $message = null): Response {
        $this->setStatus($code, $message);
        return $this;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getStatusMessage(): string {
        if (is_null($this->statusMessage)) {
            $this->statusMessage = Status::MESSAGES[$this->getStatusCode()];
        }

        return $this->statusMessage;
    }

    public function withJSON(array $body): Response {
        $this->body = json_encode($body);
        $this->setHeader("Content-Type", "application/json");
        return $this;
    }

    public function getETag(): string {
        return md5($this->getBody());
    }

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

    public function send(): void {
        $this->sendHeaders();

        echo $this->getBody();
    }
}
