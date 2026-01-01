# HTTP

[![CodeFactor](https://www.codefactor.io/repository/github/jahidulpabelislam/http/badge)](https://www.codefactor.io/repository/github/jahidulpabelislam/http)
[![Latest Stable Version](https://poser.pugx.org/jpi/http/v/stable)](https://packagist.org/packages/jpi/http)
[![Total Downloads](https://poser.pugx.org/jpi/http/downloads)](https://packagist.org/packages/jpi/http)
[![Latest Unstable Version](https://poser.pugx.org/jpi/http/v/unstable)](https://packagist.org/packages/jpi/http)
[![License](https://poser.pugx.org/jpi/http/license)](https://packagist.org/packages/jpi/http)
![GitHub last commit (branch)](https://img.shields.io/github/last-commit/jahidulpabelislam/http/1.x.svg?label=last%20activity)

A simple & lightweight HTTP library for building web applications and APIs in PHP.

This library has been kept very simple, following the KISS principle.

## Features

- **Routing**: Define routes with URL parameters using `{param}` syntax
- **Middleware Support**: Process requests through a middleware chain before reaching route handlers
- **Request Handling**: Access query parameters, POST data, JSON bodies, uploaded files, headers, and cookies
- **Response Building**: Create text or JSON responses with fluent interface for headers and caching
- **Named Routes**: Generate URLs for routes by name with parameters
- **Controller Support**: Use controller classes or closures as route handlers
- **HTTP Status Codes**: Built-in status code constants with standard messages
- **File Uploads**: Handle single and multiple file uploads with simple API

## Dependencies

- PHP 8.0+
- Composer
- [jpi/utils](https://packagist.org/packages/jpi/utils) v1

## Installation

Use [Composer](https://getcomposer.org/)

```bash
$ composer require jpi/http 
```

## Usage

This library consists of several main components that work together to handle HTTP requests and responses:

- **App**: The main application container that manages routing and middleware
- **Router**: Handles route registration and matching
- **Request**: Represents an HTTP request with access to parameters, headers, body, and files
- **Response**: Represents an HTTP response with status codes, headers, and body content
- **Route**: Defines a single route pattern with its handler
- **Middleware**: Chain of processors that can modify requests/responses

### Basic Setup

To create a basic HTTP application, you'll need to instantiate the main components:

```php
// Create a request from global variables
$request = \JPI\HTTP\Request::fromGlobals();

// Create router with 404 and 405 handlers
$router = new \JPI\HTTP\Router(
    $request,
    fn($req) => new \JPI\HTTP\Response(404, "Not Found"),
    fn($req) => new \JPI\HTTP\Response(405, "Method Not Allowed")
);

// Create the application
$app = new \JPI\HTTP\App($router);
```

### Defining Routes

Routes are defined using the `addRoute` method, which accepts a path pattern, HTTP method, callback, and optional name:

```php
// Simple GET route
$app->addRoute("/", "GET", function(\JPI\HTTP\Request $request) {
    return new \JPI\HTTP\Response(200, "Hello, World!");
});

// Route with parameters
$app->addRoute("/users/{id}/", "GET", function(\JPI\HTTP\Request $request, string $id) {
    return \JPI\HTTP\Response::json(200, ["user_id" => $id]);
});

// POST route for creating resources
$app->addRoute("/users/", "POST", function(\JPI\HTTP\Request $request) {
    $data = $request->getArrayFromBody();
    // Process the data...
    return \JPI\HTTP\Response::json(201, ["message" => "User created"]);
});

// Named route (useful for generating URLs)
$app->addRoute("/profile/{username}/", "GET", function(\JPI\HTTP\Request $request, string $username) {
    return \JPI\HTTP\Response::json(200, ["username" => $username]);
}, "user.profile");
```

### Route Parameters

Route parameters are defined using curly braces `{param}` and are passed as arguments to your route handler:

```php
$app->addRoute("/posts/{category}/{id}/", "GET", function(\JPI\HTTP\Request $request, string $category, string $id) {
    return \JPI\HTTP\Response::json(200, [
        "category" => $category,
        "post_id" => $id
    ]);
});
```

### Using Controllers

Instead of closures, you can use controller classes for better organisation:

```php
// Define your controller
class UserController {
    use \JPI\HTTP\RequestAwareTrait;
    
    public function show(string $id) {
        // Access request via $this->request
        return \JPI\HTTP\Response::json(200, ["id" => $id]);
    }
}

// Register route with controller
$app->addRoute("/users/{id}/", "GET", "UserController::show");
```

### Request Object

The Request object provides access to all incoming request data:

```php
$app->addRoute("/search/", "GET", function(\JPI\HTTP\Request $request) {
    // Query parameters
    $query = $request->getQueryParam("q", "");
    $page = $request->getQueryParam("page", "1");
    
    // Headers
    $contentType = $request->getHeaderString("Content-Type");
    
    // Method and path
    $method = $request->getMethod();
    $path = $request->getPath();
    
    // URL information
    $url = $request->getURL();
    
    // Cookies
    $cookies = $request->getCookies();
    
    return \JPI\HTTP\Response::json(200, ["query" => $query, "page" => $page]);
});

$app->addRoute("/upload/", "POST", function(\JPI\HTTP\Request $request) {
    // POST data
    $postData = $request->getPostParams();
    
    // JSON body
    $jsonData = $request->getArrayFromBody();
    
    // File uploads
    $files = $request->getFiles();
    
    // Custom attributes (set by middleware or route handlers)
    $userId = $request->getAttribute("user_id");
    
    return \JPI\HTTP\Response::json(200, ["received" => true]);
});
```

### Response Object

The Response object allows you to build HTTP responses:

```php
// Text response
$response = new \JPI\HTTP\Response(200, "Hello, World!");

// JSON response
$response = \JPI\HTTP\Response::json(200, ["message" => "Success"]);

// Fluent interface for building responses
$response = (new \JPI\HTTP\Response())
    ->withStatus(200)
    ->withHeader("Content-Type", "text/html")
    ->withBody("<h1>Hello</h1>");

// Adding cache headers
$response = \JPI\HTTP\Response::json(200, ["data" => "..."])
    ->withCacheHeaders([
        "Cache-Control" => "public, max-age=3600",
        "ETag" => true, // Automatically generated from body
    ]);
```

### Middleware

Middleware allows you to process requests before they reach your route handlers:

```php
// Create a middleware class
class AuthMiddleware implements \JPI\HTTP\RequestMiddlewareInterface {
    use \JPI\HTTP\RequestAwareTrait;
    
    public function run(\JPI\HTTP\RequestHandlerInterface $next): \JPI\HTTP\Response {
        // Check authentication
        $token = $this->request->getHeaderString("Authorization");
        
        if (!$token) {
            return new \JPI\HTTP\Response(401, "Unauthorized");
        }
        
        // Add user info to request
        $this->request->setAttribute("user_id", 123);
        
        // Continue to next middleware or route handler
        return $next->handle();
    }
}

// Add middleware to the application
$app->addMiddleware(new AuthMiddleware());
```

### Handling the Request

Once routes and middleware are configured, handle the incoming request and send the response:

```php
$response = $app->handle();
$response->send();
```

### Complete Example

Here's a complete example putting it all together:

```php
<?php

require_once "vendor/autoload.php";

// Create request from globals
$request = \JPI\HTTP\Request::fromGlobals();

// Create router with error handlers
$router = new \JPI\HTTP\Router(
    $request,
    fn($req) => \JPI\HTTP\Response::json(404, ["error" => "Not Found"]),
    fn($req) => \JPI\HTTP\Response::json(405, ["error" => "Method Not Allowed"])
);

// Create application
$app = new \JPI\HTTP\App($router);

// Define routes
$app->addRoute("/", "GET", function(\JPI\HTTP\Request $request) {
    return \JPI\HTTP\Response::json(200, ["message" => "Welcome to the API"]);
});

$app->addRoute("/users/", "GET", function(\JPI\HTTP\Request $request) {
    $page = $request->getQueryParam("page", "1");
    return \JPI\HTTP\Response::json(200, [
        "users" => [],
        "page" => (int)$page
    ]);
});

$app->addRoute("/users/{id}/", "GET", function(\JPI\HTTP\Request $request, string $id) {
    return \JPI\HTTP\Response::json(200, [
        "id" => $id,
        "name" => "Example User"
    ]);
});

$app->addRoute("/users/", "POST", function(\JPI\HTTP\Request $request) {
    $data = $request->getArrayFromBody();
    // Process creation...
    return \JPI\HTTP\Response::json(201, ["id" => "new-user-id"]);
});

// Handle request and send response
$response = $app->handle();
$response->send();
```

### Generating URLs for Named Routes

If you've given routes names, you can generate URLs for them:

```php
// Register a named route
$app->addRoute("/users/{id}/posts/{postId}/", "GET", "PostController::show", "user.post");

// Generate path
$path = $router->getPathForRoute("user.post", ["id" => "123", "postId" => "456"]);
// Result: /users/123/posts/456/

// Generate full URL
$url = $router->getURLForRoute("user.post", ["id" => "123", "postId" => "456"]);
// Result: \JPI\Utils\URL object with full URL
```

## API Reference

For a complete list of classes and methods, see the [API Reference](API.md).

## Support

If you found this library interesting or useful please spread the word about this library: share on your socials, star on GitHub, etc.

If you find any issues or have any feature requests, you can open a [issue](https://github.com/jahidulpabelislam/http/issues) or email [me @ jahidulpabelislam.com](mailto:me@jahidulpabelislam.com) :smirk:.

## Authors

-   [Jahidul Pabel Islam](https://jahidulpabelislam.com/) [<me@jahidulpabelislam.com>](mailto:me@jahidulpabelislam.com)

## License

This module is licensed under the General Public Licence - see the [licence](LICENSE.md) file for details.
