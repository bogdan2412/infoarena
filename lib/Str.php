<?php

class Str {

  static function startsWith(string $string, string $substring): bool {
    $prefix = substr($string, 0, strlen($substring));
    return $prefix == $substring;
  }

  static function endsWith(string $string, string $substring): bool {
    $lenString = strlen($string);
    $lenSubstring = strlen($substring);
    $endString = substr($string, $lenString - $lenSubstring, $lenSubstring);
    return $endString == $substring;
  }

  static function randomString(int $length): string {
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
    $sigma = strlen($alphabet);

    $result = '';
    while ($length--) {
      $result .= $alphabet[rand(0, $sigma - 1)];
    }
    return $result;
  }

  static function camelCaseToSnakeCase(string $str): string {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
  }

  static function snakeCaseToCamelCase(string $str): string {
    $words = str_replace('_', ' ', $str);
    $capitals = ucwords($words);
    $camelCase = str_replace(' ', '', $capitals);
    return $camelCase;
  }

  static function isRoundPage(string $pageName): bool {
    return self::startsWith($pageName, Config::ROUND_TEXTBLOCK_PREFIX);
  }

  static function isTaskPage(string $pageName): bool {
    return self::startsWith($pageName, Config::TASK_TEXTBLOCK_PREFIX);
  }

  static function isUserPage(string $pageName): bool {
    return self::startsWith($pageName, Config::USER_TEXTBLOCK_PREFIX);
  }

}
