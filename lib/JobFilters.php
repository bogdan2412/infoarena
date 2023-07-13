<?php

class JobFilters {
  const ACCEPTED_FILTERS = [
    'compiler' => 'CompilerJobFilter',
    'eval_msg' => 'EvalMessageJobFilter',
    'job' => 'JobIdJobFilter',
    'round' => 'RoundJobFilter',
    'score' => 'ScoreJobFilter',
    'status' => 'StatusJobFilter',
    'task' => 'TaskJobFilter',
    'task_security' => 'TaskSecurityJobFilter',
    'time' => 'TimeJobFilter',
    'user' => 'UserJobFilter',
  ];

  private array $filters;
  private ORM $query;
  private array $joins;

  function __construct(array $vars) {
    $this->filters = [];
    foreach ($vars as $var => $value) {
      if (isset(self::ACCEPTED_FILTERS[$var])) {
        $this->filters[$var] = $value;
      }
    }
  }

  static function parseFromUrl(string $url): JobFilters {
    $query = parse_url($url, PHP_URL_QUERY);
    $vars = [];
    parse_str($query, $vars);
    return new JobFilters($vars);
  }

  static function parseFromRequest(): JobFilters {
    return new JobFilters($_REQUEST);
  }

  function asArray(): array {
    return $this->filters;
  }

  private function prepareQuery() {
    $this->query = Model::factory('Job')
      ->table_alias('j')
      ->select('j.*');
    $this->joins = [];

    foreach ($this->filters as $key => $value) {
      $this->processFilter($key, $value);
    }

    $this->addJoins();
  }

  private function processFilter(string $key, string $value): void {
    $className = self::ACCEPTED_FILTERS[$key];
    $obj = new $className($value);
    $this->query = $obj->addToQueryIfNonemptyValue($this->query);
    $this->joins[$obj->getJoin()] = true;
  }

  private function addJoins() {
    foreach ($this->joins as $join => $ignored) {
      switch ($join) {
        case 'task':
          $this->query = $this->query->join(
            'ia_task', [ 'j.task_id', '=', 't.id' ], 't');
          break;
        case 'round':
          $this->query = $this->query->raw_join(
            'join ia_round',
            '(j.round_id = r.id) and (r.public_eval)',
            'r');
          break;
      }
    }
  }

  function count(): int {
    $this->prepareQuery();
    return $this->query->count();
  }

  function getAll(): array {
    $this->prepareQuery();
    return $this->query->find_many();
  }

  function getRange(int $offset, int $limit): array {
    $this->prepareQuery();
    return $this->query
      ->order_by_desc('j.id')
      ->offset($offset)
      ->limit($limit)
      ->find_many();
  }
}

abstract class AbstractJobFilter {
  protected string $value;

  final function __construct(string $value) {
    $this->value = $value;
    $this->preprocess();
  }

  function preprocess(): void {
  }

  final function addToQueryIfNonemptyValue(ORM $query): ORM {
    return ($this->value == '')
      ? $query
      : $this->addToQuery($query);
  }

  abstract function addToQuery(ORM $query): ORM;

  function getJoin(): string {
    return 'none';
  }
}

abstract class ExactOrRangeJobFilter extends AbstractJobFilter {
  function addToQuery(ORM $query): ORM {
    $parts = explode('-', $this->value, 2);

    if ((count($parts) == 1) && is_numeric($parts[0])) {
      $query = $this->addExactToQuery($query, $parts[0]);
    }

    if (count($parts) == 2) {
      if (is_numeric($parts[0])) {
        $query = $this->addGteToQuery($query, $parts[0]);
      }
      if (is_numeric($parts[1])) {
        $query = $this->addLteToQuery($query, $parts[1]);
      }
    }

    return $query;
  }

  abstract function addExactToQuery(ORM $query, string $value): ORM;
  abstract function addGteToQuery(ORM $query, string $value): ORM;
  abstract function addLteToQuery(ORM $query, string $value): ORM;
}

class CompilerJobFilter extends AbstractJobFilter {
  function addToQuery(ORM $query): ORM {
    return $query->where('j.compiler_id', $this->value);
  }
}

class EvalMessageJobFilter extends AbstractJobFilter {
  function addToQuery(ORM $query): ORM {
    return $query->where_like('j.eval_message', $this->value . '%');
  }
}

class JobIdJobFilter extends ExactOrRangeJobFilter {

  function addExactToQuery(ORM $query, string $value): ORM {
    return $query->where('j.id', $value);
  }

  function addGteToQuery(ORM $query, string $value): ORM {
    return $query->where_gte('j.id', $value);
  }

  function addLteToQuery(ORM $query, string $value): ORM {
    return $query->where_lte('j.id', $value);
  }
}

class RoundJobFilter extends AbstractJobFilter {
  function addToQuery(ORM $query): ORM {
    return $query->where('j.round_id', $this->value);
  }
}

class ScoreJobFilter extends ExactOrRangeJobFilter {
  function addExactToQuery(ORM $query, string $value): ORM {
    return $query->where('j.score', $value);
  }

  function addGteToQuery(ORM $query, string $value): ORM {
    return $query->where_gte('j.score', $value);
  }

  function addLteToQuery(ORM $query, string $value): ORM {
    return $query->where_lte('j.score', $value);
  }

  // Necessary to ensure that the round has public evaluation.
  function getJoin(): string {
    return 'round';
  }
}

class StatusJobFilter extends AbstractJobFilter {
  function addToQuery(ORM $query): ORM {
    return $query->where('j.status', $this->value);
  }
}

class TaskJobFilter extends AbstractJobFilter {
  function addToQuery(ORM $query): ORM {
    return $query->where('j.task_id', $this->value);
  }
}

class TaskSecurityJobFilter extends AbstractJobFilter {
  function preprocess(): void {
    $this->value = Task::getValidSecurity($this->value);
  }

  function addToQuery(ORM $query): ORM {
    return $query->where('t.security', $this->value);
  }

  function getJoin(): string {
    return 'task';
  }
}

class TimeJobFilter extends ExactOrRangeJobFilter {
  private function padAndConvert(string $dateOrDateTime, string $time): string {
    $dateTime = preg_match('/^\d{8}$/', $dateOrDateTime)
      ? ($dateOrDateTime . $time)
      : $dateOrDateTime;

    if (preg_match('/^\d{14}$/', $dateTime)) {
      $dateTime = preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/',
                                     '$1-$2-$3 $4:$5:$6',
                                     $dateTime);
      return Time::mysqlLocalToUTC($dateTime);
    } else {
      return '';
    }
  }

  function addExactToQuery(ORM $query, string $value): ORM {
    $value = $this->padAndConvert($value, '000000');
    if ($value) {
      $query = $query->where('j.submit_time', $value);
    }
    return $query;
  }

  function addGteToQuery(ORM $query, string $value): ORM {
    $value = $this->padAndConvert($value, '000000');
    if ($value) {
      $query = $query->where_gte('j.submit_time', $value);
    }
    return $query;
  }

  function addLteToQuery(ORM $query, string $value): ORM {
    $value = $this->padAndConvert($value, '235959');
    if ($value) {
      $query = $query->where_lte('j.submit_time', $value);
    }
    return $query;
  }
}

class UserJobFilter extends AbstractJobFilter {
  function preprocess(): void {
    $this->value = User::getIdFromUsername($this->value);
  }

  function addToQuery(ORM $query): ORM {
    return $query->where('j.user_id', $this->value);
  }
}
