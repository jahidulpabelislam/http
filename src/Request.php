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

    public function __construct() {
        $this->server = new Collection($_SERVER);

        $this->cookies = new Collection($_COOKIE);

        $this->method = strtoupper($this->server->get("REQUEST_METHOD"));

        $this->path = parse_url($this->server->get("REQUEST_URI"), PHP_URL_PATH);

        // Get the individual parts of the request URI as an array
        $path = URL::removeSlashes($this->path);
        $this->pathParts = explode("/", $path);

        $this->params = self::sanitizeData($_GET);
        $this->post = self::sanitizeData($_POST);

        $this->body = file_get_contents("php://input");

        $files = [];
        foreach ($_FILES as $key => $item) {
            $files[$key] = $this->normaliseFileItem($item);
        }
        $this->files = $files;

        $this->headers = new Headers(apache_request_headers());

        $this->identifiers = new Collection();

        $this->protocolVersion = 1.1;
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
        return $this->server;
    }

    public function getCookies(): Collection {
        return $this->cookies;
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
        return $this->params;
    }

    public function hasParam(string $param): bool {
        return isset($this->params[$param]);
    }

    public function getParam(string $param, $default = null) {
        return $this->params->get($param, $default);
    }

    public function getPost(): Collection {
        return $this->post;
    }

    /**
     * @return UploadedFile[]
     */
    public function getFiles(): array {
        return $this->files;
    }

    public function getIdentifiers(): Collection {
        return $this->identifiers;
    }

    public function getIdentifier(string $identifier, $default = null) {
        return $this->identifiers->get($identifier, $default);
    }
}
