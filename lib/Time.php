<?php

class Time {

  private static $fullDateTimeFormatter;

  static function init(): void {
    self::$fullDateTimeFormatter = new IntlDateFormatter(
      Config::LOCALE,
      IntlDateFormatter::NONE,
      IntlDateFormatter::NONE,
      IA_DATE_DEFAULT_TIMEZONE,
      null,
      'd MMM yyyy HH:mm:ss'
    );
  }

  static function fullDateTime(string $mysqlDateTime): string {
    $timestamp = strtotime($mysqlDateTime);
    return self::$fullDateTimeFormatter->format($timestamp);
  }
}
