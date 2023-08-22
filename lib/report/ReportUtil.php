<?php

class ReportUtil {

  const EXCLUDED_CLASS_NAMES = [ 'Report', 'ReportUtil' ];

  static function getCachedTotal(): int {
    $result = Model::factory('Variable')
      ->select_expr('sum(value)', 'sum')
      ->where_like('name', 'Count.%')
      ->find_one();

    return $result->sum;
  }

  static function getAll(): array {
    $objects = [];

    foreach (self::getAllClassNames() as $className) {
      $objects[] = new $className();
    }

    return $objects;
  }

  private static function getAllClassNames(): array {
    $wildcard = __DIR__ . '/Report*.php';
    $files = glob($wildcard);

    $classNames = [];
    foreach ($files as $file) {
      $className = pathinfo($file, PATHINFO_FILENAME);
      if (!in_array($className, self::EXCLUDED_CLASS_NAMES)) {
        $classNames[] = $className;
      }
    }
    return $classNames;
  }

  static function getAllPositive(): array {
    return array_filter(self::getAll(), function(Report $report): bool {
      return ($report->getCachedCount() > 0);
    });
  }

}
