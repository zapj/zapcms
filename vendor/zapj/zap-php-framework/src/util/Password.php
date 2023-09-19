<?php

namespace zap\util;

class Password
{
    static public function hash($password,$algo = PASSWORD_DEFAULT) {
        return password_hash($password,$algo);
    }

    static public function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}