<?php

namespace zap\db;

class Expr
{
    public $raw;

    public function __construct($value) {
        $this->raw = $value;
    }

    /**
     * Make Expr
     * @param mixed $value
     * @return Expr
     */
    public static function make($value) {
        return new static($value);
    }

    public function __toString() {
        return $this->raw;
    }
}