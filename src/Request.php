<?php

declare(strict_types=1);

namespace JPI\HTTP;

use JPI\Utils\Collection;
use JPI\Utils\URL;

class Request extends Message {

    protected $server;

    protected $cookies;

    protected $method;

    protected $path;
    protected $pathParts;

    protected $params;
    protected $post;

    protected $files;

    protected $url;

    protected $identifiers;

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
        array $server,
        array $cookies,
        array $params,
        array $post,
        string $body,
        array $files,
        array $headers
    ) {
        $this->server = new Collection($server);

        $this->cookies = new Collection($cookies);

        $this->method = strtoupper($this->server->get("REQUEST_METHOD"));

        $this->path = parse_url($this->server->get("REQUEST_URI"), PHP_URL_PATH);

        // Get the individual parts of the request URI as an array
        $path = URL::removeSlashes($this->path);
        $this->pathParts = explode("/", $path);

        $this->params = self::sanitizeData($params);
        $this->post = self::sanitizeData($post);

        $this->body = $body;

        $this->files = [];
        foreach ($files as $key => $item) {
            $this->files[$key] = $this->normaliseFileItem($item);
        }

        $this->headers = new Headers($headers);

        $this->identifiers = new Collection();

        $url = new URL($this->server->get("REQUEST_URI"));
        $url->setScheme($this->server->get("HTTPS") !== "off" ? "https" : "http");
        $url->setHost($this->server->get("HTTP_HOST"));

        $this->url = $url;

        $this->protocolVersion = 1.1;
    }

    public static function fromGlobals(): Request {
        return new static(
            $_SERVER,
            $_COOKIE,
            $_GET,
            $_POST,
            file_get_contents("php://input"),
            $_FILES,
            apache_request_headers()
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

    public function getServer(): Collection {
        return clone $this->server;
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

    public function getParams(): Collection {
        return clone $this->params;
    }

    public function hasParam(string $param): bool {
        return isset($this->params[$param]);
    }

    public function getParam(string $param, $default = null) {
        return $this->params->get($param, $default);
    }

    public function getPost(): Collection {
        return clone $this->post;
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

    public function getIdentifiers(): Collection {
        return clone $this->identifiers;
    }

    public function getIdentifier(string $identifier, $default = null) {
        return $this->identifiers->get($identifier, $default);
    }
}
