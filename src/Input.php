<?php

namespace JPI\HTTP;

use JPI\Utils\Collection;

class Input extends Collection {

    protected $raw;

    public function __construct(array $input) {
        $this->raw = $input;

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $this->set($key, new static($value));
            }
            else {
                $this->set($key, urldecode(stripslashes(trim($value))));
            }
        }
    }
}
