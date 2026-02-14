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
            $this->set($key, $value);
        }
    }

    public function set(string|int $key, $item): void {
        if (is_array($item)) {
            $item = new static($item);
        }
        else if (!$item instanceof self) {
            $item = urldecode(stripslashes(trim((string)$item)));
        }

        parent::set($key, $item);
    }
}
