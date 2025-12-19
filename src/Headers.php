<?php

declare(strict_types=1);

namespace JPI\HTTP;

use JPI\Utils\Collection;

/**
 * Manages HTTP headers with support for multiple values per header.
 *
 * Extends Collection to store headers as arrays of values, allowing
 * multiple values for the same header name (e.g., multiple Set-Cookie headers).
 */
class Headers extends Collection {

    /**
     * Construct Headers collection.
     *
     * Ensures all header values are stored as arrays for consistency.
     */
    public function __construct(array $items = []) {
        foreach ($items as $header => $value) {
            if (!is_array($value)) {
                $items[$header] = [$value];
            }
        }

        parent::__construct($items);
    }

    /**
     * Set a header value.
     *
     * Ensures the value is stored as an array.
     */
    public function set(string|int $header, $value): void {
        if (!is_array($value)) {
            $value = [$value];
        }

        parent::set($header, $value);
    }
}
