<?php

class Str {

  static function startsWith(string $string, string $substring): bool {
    $prefix = substr($string, 0, strlen($substring));
    return $prefix == $substring;
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
