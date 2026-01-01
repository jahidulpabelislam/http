# API Reference

## Classes

### App

The main application class that manages routing and middleware.

**Methods:**
- `handle(): \JPI\HTTP\Response`: Process the request and return a response

### Router

Handles route registration and matching.

**Methods:**
- `handle(): \JPI\HTTP\Response`: Match and execute the appropriate route

### Request

Represents an HTTP request.

**Static Methods:**
- `fromGlobals(): \JPI\HTTP\Request`: Create a request from PHP globals (`$_SERVER`, `$_GET`, `$_POST`, etc.)

**Methods:**
- `getMethod(): string`: Get the HTTP method (GET, POST, etc.)
- `getPath(): string`: Get the request path
- `getPathParts(): array`: Get the path split into parts
- `getPathPart(int $index): ?string`: Get a specific part of the path
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

Represents an HTTP response.

**Static Methods:**
- `json(int $statusCode = 500, array $body = [], array $headers = [], float $protocolVersion = 1.1): \JPI\HTTP\Response`: Create a JSON response

**Methods:**
- `__construct(int $statusCode = 500, string $body = "", array $headers = [], float $protocolVersion = 1.1)`: Create a response
- `setStatus(int $code, ?string $message = null): void`: Set the status code
- `withStatus(int $code, ?string $message = null): \JPI\HTTP\Response`: Set status (fluent)
- `getStatusCode(): int`: Get the status code
- `getStatusMessage(): string`: Get the status message
- `withJSON(array $body): \JPI\HTTP\Response`: Set JSON body (fluent)
- `setCacheHeaders(array $headers): void`: Set cache headers
- `withCacheHeaders(array $headers): \JPI\HTTP\Response`: Set cache headers (fluent)
- `getETag(): string`: Get MD5 hash of body as ETag
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
