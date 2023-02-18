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

    protected $bodyArray = null;

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

        $this->queryParams = new Input($queryParams);
        $this->postParams = new Input($postParams);

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

    public function __clone() {
        $this->serverParams = clone $this->serverParams;
        $this->cookies = clone $this->cookies;
        $this->queryParams = clone $this->queryParams;
        $this->postParams = clone $this->postParams;
        $this->headers = clone $this->headers;
        $this->attributes = clone $this->attributes;
        $this->url = clone $this->url;

        if ($this->bodyArray) {
            $this->bodyArray = clone $this->bodyArray;
        }
    }

    /**
     * @param array $item
     * @return UploadedFile|array
     * @author Jahidul Islam <jahidul@d3r.com>
     */
    protected function normaliseFileItem(array $item) {
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

    public function setQueryParams(Input $params): void {
        $this->queryParams =  $params;
    }

    public function getQueryParams(): Input {
        return clone $this->queryParams;
    }

    public function hasQueryParam(string $param): bool {
        return isset($this->queryParams[$param]);
    }

    public function getQueryParam(string $param, $default = null) {
        return $this->queryParams->get($param, $default);
    }

    public function getPostParams(): Input {
        return clone $this->postParams;
    }

    public function getArrayFromBody(): Input {
        if ($this->bodyArray === null) {
            $this->bodyArray = new Input(json_decode($this->getBody(), true));
        }

        return clone $this->bodyArray;
    }

    /**
     * @return UploadedFile[]
     */
    public function getFiles(): array {
        return $this->files;
    }

    public function getURL(): URL {
        return clone $this->url;
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
