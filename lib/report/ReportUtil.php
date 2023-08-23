<?php

class ReportUtil {

  const EXCLUDED_CLASS_NAMES = [ 'Report', 'ReportUtil' ];

  static function getCachedTotal(): int {
    $result = Model::factory('Variable')
      ->select_expr('sum(value)', 'sum')
      ->where_like('name', 'Count.%')
      ->find_one();

    return $result->sum ?? 0;
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

  static function getByUrlName(string $urlName): ?Report {
    $className = self::urlNameToClassName($urlName);

    if (class_exists($className)) {
      return new $className();
    } else {
      return null;
    }
  }

  static function classNameToUrlName(string $className): string {
    $snakeCase = Str::camelCaseToSnakeCase($className);
    $posOfUnderscore = strpos($snakeCase, '_');
    $rest = substr($snakeCase, 1 + $posOfUnderscore);
    return $rest;
  }

  static function urlNameToClassName(string $urlName): string {
    $camelCase = Str::snakeCaseToCamelCase($urlName);
    return 'Report' . $camelCase;
  }

}
