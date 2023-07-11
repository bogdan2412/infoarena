<?php

class JobFilters {

  const ACCEPTED_FILTERS = [
    'compiler',
    'eval_msg',
    'job_begin',
    'job_end',
    'job_id',
    'round',
    'score_begin',
    'score_end',
    'status',
    'task',
    'task_security',
    'time_begin',
    'time_end',
    'user',
  ];

  static function parseFromUrl(string $url): array {
    $query = parse_url($url, PHP_URL_QUERY);
    $vars = [];
    parse_str($query, $vars);
    return self::parseFromArray($vars);
  }

  static function parseFromRequest(): array {
    return self::parseFromArray($_REQUEST);
  }

  private static function parseFromArray(array $vars): array {
    $result = [];
    foreach ($vars as $var => $value) {
      if (in_array($var, self::ACCEPTED_FILTERS)) {
        $result[$var] = $value;
      }
    }
    return $result;
  }
}
