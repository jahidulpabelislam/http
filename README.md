# HTTP

[![CodeFactor](https://www.codefactor.io/repository/github/jahidulpabelislam/http/badge)](https://www.codefactor.io/repository/github/jahidulpabelislam/http)
[![Latest Stable Version](https://poser.pugx.org/jpi/http/v/stable)](https://packagist.org/packages/jpi/http)
[![Total Downloads](https://poser.pugx.org/jpi/http/downloads)](https://packagist.org/packages/jpi/http)
[![Latest Unstable Version](https://poser.pugx.org/jpi/http/v/unstable)](https://packagist.org/packages/jpi/http)
[![License](https://poser.pugx.org/jpi/http/license)](https://packagist.org/packages/jpi/http)
![GitHub last commit (branch)](https://img.shields.io/github/last-commit/jahidulpabelislam/http/1.x.svg?label=last%20activity)

A simple & lightweight HTTP library providing components to build web applications and APIs in PHP.

This library has been kept very simple, following the KISS principle.

## Features

- **Routing**: Routes with parameters, controller classes or closures as handlers, named routes for URL generation
- **Middleware Support**
- **Request Handling**: Access query parameters, input, uploaded files, headers, and cookies
- **Response Building**: Fluent interface to create text or JSON responses

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
$request = \JPI\HTTP\Request::fromGlobals();

$router = new \JPI\HTTP\Router(
    $request,
    function (\JPI\HTTP\Request $request): \JPI\HTTP\Response {
        return new \JPI\HTTP\Response(404, "Not Found");
    },
    function (\JPI\HTTP\Request $request): \JPI\HTTP\Response {
        return new \JPI\HTTP\Response(405, "Method Not Allowed");
    }
);

$app = new \JPI\HTTP\App($router);
```

### Defining Routes

Routes are defined using the `addRoute` method (either `App` & `Router`), which accepts a path pattern, HTTP method, callback, and optional name:

```php
// Simple GET route
$app->addRoute("/", "GET", function (\JPI\HTTP\Request $request): \JPI\HTTP\Response {
    return new \JPI\HTTP\Response(200, "Hello, World!");
});

// POST route
$app->addRoute("/posts/", "POST", function (\JPI\HTTP\Request $request): \JPI\HTTP\Response {
    // Process the data...
    return new \JPI\HTTP\Response(201, "Post created");
});
```

### Using Controllers

Instead of closures, you can use classes for better organisation, format is `{class}::{method}` (method must be public):

```php
final class PostController {

    use \JPI\HTTP\RequestAwareTrait;

    public function show(string $id): \JPI\HTTP\Response {
        // Access request via $this->getRequest()
        return new \JPI\HTTP\Response(200, "Post");
    }
}

$app->addRoute("/posts/{id}/", "GET", "PostController::show");
```

### Route Parameters

Route parameters are defined using curly braces `{param}` and are passed as arguments to your route handler:

```php
$app->addRoute("/posts/{category}/{id}/", "GET", function (\JPI\HTTP\Request $request, string $category, string $id): \JPI\HTTP\Response {
    return new \JPI\HTTP\Response(200, "Post");
});
```

### Named Routes

You can assign names to routes, which allows you to generate URLs for them later:

```php
$app->addRoute("/posts/{slug}/", "GET", function(\JPI\HTTP\Request $request, string $slug): \JPI\HTTP\Response {
    return new \JPI\HTTP\Response(200, "Post");
}, "post.show");
```

To generate URLs for named routes:

```php
$path = $router->getPathForRoute("post.show", ["slug" => "my-post"]);
// Result: /posts/my-post/

$url = $router->getURLForRoute("post.show", ["slug" => "my-post"]);
// Result: \JPI\Utils\URL object with full URL
```

### Request Object

The Request object provides access to all incoming request data:

```php
// Query parameters (returns string with default fallback)
$query = $request->getQueryParam("q", "");

// POST form data (returns associative array)
$postData = $request->getPostParams();

// JSON request body (returns parsed array)
$jsonData = $request->getArrayFromBody();

// Uploaded files (returns array of UploadedFile objects)
$files = $request->getFiles();

// Request headers (returns string or null)
$contentType = $request->getHeaderString("Content-Type");

// HTTP method (returns string: GET, POST, etc.)
$method = $request->getMethod();

// Request path (returns string)
$path = $request->getPath();

// Full URL (returns \JPI\Utils\URL object)
$url = $request->getURL();

// Cookies (returns associative array)
$cookies = $request->getCookies();

// Custom attributes set by middleware (returns mixed)
$foo = $request->getAttribute("foo");
```

### Response Object

The Response object allows you to build HTTP responses:

```php
$response = new \JPI\HTTP\Response(200, "Hello, World!");

$response = \JPI\HTTP\Response::json(200, ["message" => "Success"]);

$response = (new \JPI\HTTP\Response())
    ->withStatus(200)
    ->withHeader("Content-Type", "text/html")
    ->withBody("<h1>Hello</h1>")
    ->withCacheHeaders([
        "Cache-Control" => "public, max-age=3600",
        "ETag" => true, // Automatically generated from body
    ])
;
```

### Middleware

Middleware allows you to process requests before they reach your route handlers. You can add a middle using `addMiddleware` on the app or pass an array to the App constructor:

```php
class AuthMiddleware implements \JPI\HTTP\RequestMiddlewareInterface {

    use \JPI\HTTP\RequestAwareTrait;

    public function run(\JPI\HTTP\RequestHandlerInterface $next): \JPI\HTTP\Response {
        $token = $this->getRequest()->getHeaderString("Authorization");

        if (!$token) {
            return new \JPI\HTTP\Response(401, "Unauthorized");
        }

        // Do authentication...

        $this->getRequest()->setAttribute("user_id", 123);

        return $next->handle();
    }
}

$app->addMiddleware(new AuthMiddleware());

// Or pass an array of middlewares to the App constructor
$app = new \JPI\HTTP\App($router, [new AuthMiddleware(), new AnotherMiddleware()]);
```

### Handling the Request

Once routes and middleware are configured, handle the incoming request and send the response:

```php
$response = $app->handle();
$response->send();
```

## API Reference

For a complete list of classes and methods, see the [API Reference](API.md).

## Support

If you found this library interesting or useful please spread the word about this library: share on your socials, star on GitHub, etc.

If you find any issues or have any feature requests, you can open a [issue](https://github.com/jahidulpabelislam/http/issues) or email [me @ jahidulpabelislam.com](mailto:me@jahidulpabelislam.com) :smirk:.

## Authors

- [Jahidul Pabel Islam](https://jahidulpabelislam.com/) [<me@jahidulpabelislam.com>](mailto:me@jahidulpabelislam.com)

## License

This module is licensed under the General Public Licence - see the [licence](LICENSE.md) file for details.
