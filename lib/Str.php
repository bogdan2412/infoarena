<?php

class Str {

  static function startsWith(string $string, string $substring): bool {
    $prefix = substr($string, 0, strlen($substring));
    return $prefix == $substring;
  }

}
