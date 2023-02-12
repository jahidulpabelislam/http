<?php

declare(strict_types=1);

namespace JPI\HTTP;

use JPI\Utils\Collection;
use JPI\Utils\URL;

class Request extends Message {

    protected $serverParams;

    protected $cookies;

    protected $method;

    protected $path;
    protected $pathParts;

    protected $queryParams;

    protected $postParams;

    protected $files;

    protected $url;

    protected $attributes;

    /**
     * @param $value array|string
     * @return Collection|string
     */
    private static function sanitizeData($value) {
        if (is_array($value)) {
            $newArrayValues = new Collection();
            foreach ($value as $subKey => $subValue) {
                $newArrayValues[(string)$subKey] = self::sanitizeData($subValue);
            }
            $value = $newArrayValues;
        }
        else if (is_string($value)) {
            $value = urldecode(stripslashes(trim($value)));
        }

        return $value;
    }

    public function __construct(
        array $serverParams,
        array $headers,
        array $queryParams,
        array $postParams,
        array $cookies = [],
        string $body = "",
        array $files = []
    ) {
        $this->serverParams = new Collection($serverParams);

        $this->cookies = new Collection($cookies);

        $this->method = strtoupper($this->getServerParam("REQUEST_METHOD"));

        $this->path = parse_url($this->getServerParam("REQUEST_URI"), PHP_URL_PATH);

        // Get the individual parts of the request URI as an array
        $path = URL::removeSlashes($this->path);
        $this->pathParts = explode("/", $path);

        $this->queryParams = self::sanitizeData($queryParams);
        $this->postParams = self::sanitizeData($postParams);

        $this->body = $body;

        $this->files = [];
        foreach ($files as $key => $item) {
            $this->files[$key] = $this->normaliseFileItem($item);
        }

        $this->headers = new Headers($headers);

        $this->attributes = new Collection();

        $url = new URL($this->getServerParam("REQUEST_URI"));
        $url->setScheme($this->getServerParam("HTTPS") !== "off" ? "https" : "http");
        $url->setHost($this->getServerParam("HTTP_HOST"));

        $this->url = $url;

        $this->protocolVersion = 1.1;
    }

    public static function fromGlobals(): Request {
        return new static(
            $_SERVER,
            apache_request_headers(),
            $_GET,
            $_POST,
            $_COOKIE,
            file_get_contents("php://input"),
            $_FILES
        );
    }

    /**
     * @param array $item
     * @return UploadedFile|array
     * @author Jahidul Islam <jahidul@d3r.com>
     */
    private function normaliseFileItem(array $item) {
        if (!is_array($item["tmp_name"])) {
            return new UploadedFile(
                $item["name"],
                $item["size"],
                $item["type"],
                $item["error"],
                $item["tmp_name"]
            );
        }

        $normalised = [];

        foreach (array_keys($item["tmp_name"]) as $key) {
            $normalised[$key] = $this->normaliseFileItem([
                "tmp_name" => $item["tmp_name"][$key],
                "size" => $item["size"][$key],
                "error" => $item["error"][$key],
                "name" => $item["name"][$key],
                "type" => $item["type"][$key],
            ]);
        }

        return $normalised;
    }

    public function getServerParams(): Collection {
        return clone $this->serverParams;
    }

    public function getServerParam(string $param, string $default = ""): string {
        return $this->serverParams->get($param, $default);
    }

    public function getCookies(): Collection {
        return clone $this->cookies;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getPathParts(): array {
        return $this->pathParts;
    }

    public function getPathPart(int $index): ?string {
        return $this->pathParts[$index] ?? null;
    }

    public function setQueryParams(Collection $params): void {
        $this->queryParams =  $params;
    }

    public function getQueryParams(): Collection {
        return clone $this->queryParams;
    }

    public function hasQueryParam(string $param): bool {
        return isset($this->queryParams[$param]);
    }

    public function getQueryParam(string $param, $default = null) {
        return $this->queryParams->get($param, $default);
    }

    public function getPostParams(): Collection {
        return clone $this->postParams;
    }

    /**
     * @return UploadedFile[]
     */
    public function getFiles(): array {
        return $this->files;
    }

    public function getURL(): URL {
        return $this->url;
    }

    public function setAttribute(string $attribute, $value): void {
        $this->attributes->set($attribute, $value);
    }

    public function getAttributes(): Collection {
        return clone $this->attributes;
    }

    public function getAttribute(string $attribute, $default = null) {
        return $this->attributes->get($attribute, $default);
    }
}
