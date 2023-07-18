<?php

class Request {

  static function get(string $name, string $default = '') {
    // PHP does this to submitted variable names...
    // https://www.php.net/manual/en/language.variables.external.php
    $name = str_replace('.', '_', $name);

    return $_REQUEST[$name] ?? $default;
  }

  static function isWeb(): bool {
    return php_sapi_name() != 'cli';
  }

}
