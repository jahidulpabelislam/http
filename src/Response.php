<?php

namespace JPI\HTTP;

use DateTime;
use DateTimeZone;

class Response {

    protected $statusCode;
    protected $statusMessage = null;

    protected $content;

    public $headers;

    public function __construct(int $statusCode = 500, string $content = null, array $headers = []) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = new Headers($headers);
    }

    public static function json(int $statusCode = 500, array $content = [], array $headers = []): Response {
        return new static($statusCode, json_encode($content), $headers);
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

    public function addHeader(string $header, $value): void {
        $this->headers->set($header, $value);
    }

    public function withHeader(string $header, $value): Response {
        $this->addHeader($header, $value);
        return $this;
    }

    public function getHeaders(): Headers {
        return $this->headers;
    }

    public function setContent(?string $content): void {
        $this->content = $content;
    }

    public function withContent(?string $content): Response {
        $this->setContent($content);
        return $this;
    }

    public function getContent(): ?string {
        return $this->content;
    }

    public function getETag(): string {
        return md5($this->getContent());
    }

    protected function sendHeaders(): void {
        if (!is_null($this->content)) {
            foreach ($this->headers as $name => $value) {
                if (is_array($value)) {
                    $value = implode(", ", $value);
                }

                header("$name: $value");
            }
        }

        header("HTTP/1.1 {$this->getStatusCode()} {$this->getStatusMessage()}");
    }

    public function send(): void {
        $this->sendHeaders();

        echo $this->getContent();
    }
}
