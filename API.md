# API Reference

`Headers` & `Input` classes work like a normal array just with some extra methods, see https://github.com/jahidulpabelislam/utils?tab=readme-ov-file#collection for more details.

See https://github.com/jahidulpabelislam/utils?tab=readme-ov-file#url for more details on the `URL` class.

## Classes

### App

The main application class that manages routing and middleware.

**Constructor:**
- `__construct(\JPI\HTTP\Router $router, array $middlewares = [])`: Create a new application with a router and optional middlewares

**Methods:**
- `getRequest(): \JPI\HTTP\Request`: Get the current request
- `addRoute(string $path, string $method, callable|string $callback, ?string $name = null): void`: Register a route
- `addMiddleware(\JPI\HTTP\RequestMiddlewareInterface $middleware): void`: Add a middleware
- `handle(): \JPI\HTTP\Response`: Process the request and return a response

### Router

Handles route registration and matching.

**Constructor:**
- `__construct(\JPI\HTTP\Request $request, callable $notFoundHandler, callable $methodNotAllowedHandler)`: Create a new router

**Methods:**
- `getRequest(): \JPI\HTTP\Request`: Get the current request
- `addRoute(string $pattern, string $method, callable|string $callback, ?string $name = null): void`: Register a route
- `getPathForRoute(string $name, array $params): string`: Generate a path for a named route
- `getURLForRoute(string $name, array $params): \JPI\Utils\URL`: Generate a full URL for a named route

### Message

Base class for Request and Response with common HTTP message functionality.

**Constructor:**
- `__construct(array $headers = [], string $body = "", float $protocolVersion = 1.1)`: Create a message

**Methods:**
- `getProtocolVersion(): float`: Get the HTTP protocol version
- `getHeaders(): \JPI\HTTP\Headers`: Get all headers
- `addHeader(string $header, Stringable|string $newValue): void`: Add a header value
- `setHeader(string $header, array|Stringable|string $value): void`: Set a header
- `withHeader(string $header, array|Stringable|string $value, bool $add = false): \JPI\HTTP\Message`: Set header (fluent)
- `removeHeader(string $header): void`: Remove a header
- `hasHeader(string $name): bool`: Check if header exists
- `getHeader(string $name): array`: Get header values as array
- `getHeaderString(string $name): string`: Get header values as string
- `setBody(string $body): void`: Set the message body
- `getBody(): string`: Get the message body
- `withBody(string $body): \JPI\HTTP\Message`: Set body (fluent)

### Request

Represents an HTTP request. Extends Message.

**Static Methods:**
- `fromGlobals(): \JPI\HTTP\Request`: Create a request from PHP globals (`$_SERVER`, `$_GET`, `$_POST`, etc.)

**Constructor:**
- `__construct(string $method, string $path, array $queryParams = [], array $postParams = [], array $files = [], array $cookies = [], array $server = [], array $headers = [], string $body = "", float $protocolVersion = 1.1)`: Create a request

**Methods:**
- `getMethod(): string`: Get the HTTP method (GET, POST, etc.)
- `getPath(): string`: Get the request path
- `getPathParts(): array`: Get the path split into parts
- `getPathPart(int $index): ?string`: Get a specific part of the path
- `setQueryParams(\JPI\HTTP\Input $params): void`: Set query parameters
- `getQueryParams(): \JPI\HTTP\Input`: Get all query parameters
- `getQueryParam(string $param, $default = null)`: Get a specific query parameter
- `hasQueryParam(string $param): bool`: Check if a query parameter exists
- `getPostParams(): \JPI\HTTP\Input`: Get all POST parameters
- `getArrayFromBody(): \JPI\HTTP\Input`: Parse JSON body as array
- `getFiles(): array`: Get uploaded files
- `getCookies(): \JPI\Utils\Collection`: Get cookies
- `getServerParams(): \JPI\Utils\Collection`: Get server parameters
- `getServerParam(string $param, string $default = ""): string`: Get a specific server parameter
- `getURL(): \JPI\Utils\URL`: Get the full request URL
- `setAttribute(string $attribute, $value): void`: Set a custom attribute
- `getAttribute(string $attribute, $default = null)`: Get a custom attribute
- `getAttributes(): \JPI\Utils\Collection`: Get all custom attributes
- `makeURL(string $path): \JPI\Utils\URL`: Create a new URL based on the current request

### Response

Represents an HTTP response. Extends `Message`.

**Static Methods:**
- `json(int $statusCode = 500, array $body = [], array $headers = [], float $protocolVersion = 1.1): \JPI\HTTP\Response`: Create a JSON response

**Constructor:**
- `__construct(int $statusCode = 500, string $body = "", array $headers = [], float $protocolVersion = 1.1)`: Create a response

**Methods:**
- `setStatus(int $code, ?string $message = null): void`: Set the status code
- `withStatus(int $code, ?string $message = null): \JPI\HTTP\Response`: Set status (fluent)
- `getStatusCode(): int`: Get the status code
- `getStatusMessage(): string`: Get the status message
- `withJSON(array $body): \JPI\HTTP\Response`: Set JSON body (fluent)
- `setCacheHeaders(array $headers): void`: Set cache headers
- `withCacheHeaders(array $headers): \JPI\HTTP\Response`: Set cache headers (fluent)
- `getETag(): string`: Get MD5 hash of body as `ETag`
- `send(): void`: Send the response to the client

### UploadedFile

Represents an uploaded file.

**Methods:**
- `getFilename(): string`: Get the original filename
- `getSize(): int`: Get the file size in bytes
- `getMediaType(): string`: Get the MIME type
- `getErrorCode(): int`: Get the upload error code
- `getTempName(): string`: Get the temporary file path
- `saveTo(string $targetPath): bool`: Move the uploaded file to a target location
