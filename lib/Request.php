<?php

class Request {

  static function get(string $name, $default = '') {
    // PHP does this to submitted variable names...
    // https://www.php.net/manual/en/language.variables.external.php
    $name = str_replace('.', '_', $name);

    return $_REQUEST[$name] ?? $default;
  }

  static function has(string $name): bool {
    return array_key_exists($name, $_REQUEST);
  }

  /* Use when the parameter is expected to have array type. */
  static function getArray(string $name): array {
    return self::get($name, []);
  }

  static function isWeb(): bool {
    return php_sapi_name() != 'cli';
  }

  static function isPost(): bool {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

}
