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

  static function mysqlLocalToUTC(string $localDateTime): string {
    $localTZ = new DateTimeZone(IA_DATE_DEFAULT_TIMEZONE);
    $d = new DateTime($localDateTime, $localTZ);
    $d->setTimeZone(new DateTimeZone('UTC'));
    return $d->format('Y-m-d H:i:s');
  }
}
