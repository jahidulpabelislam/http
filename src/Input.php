<?php

declare(strict_types=1);

namespace JPI\HTTP;

use JPI\Utils\Collection;

/**
 * Represents sanitised input data from query parameters, POST data, or JSON bodies.
 *
 * Automatically processes input values by decoding URLs, stripping slashes, and trimming.
 * Nested arrays are recursively converted to Input instances.
 */
class Input extends Collection {

    public function __construct(protected array $raw) {
        parent::__construct([]);

        foreach ($raw as $key => $value) {
            if (is_array($value)) {
                $this->set($key, new static($value));
            }
            else {
                $this->set($key, urldecode(stripslashes(trim((string)$value))));
            }
        }
    }
}
