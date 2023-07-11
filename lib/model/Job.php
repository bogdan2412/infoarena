<?php

class Job extends Base {

  public static $_table = 'ia_job';

  // Note: filtering by score requires that the round is publicly evaluated.
  const FILTERS = [
    // preprocessing function, field, operator, join
    'compiler' => [ null, 'j.compiler_id', '=', null ],
    'eval_msg' => [ 'Job::prependPercent', 'j.eval_message', 'like', null ],
    'job_begin' => [ null, 'j.id', '>=', null ],
    'job_end' => [ null, 'j.id', '<=', null ],
    'job_id' => [ null, 'j.id', '=', null ],
    'round' => [ null, 'j.round_id', '=', null ],
    'score_begin' => [ null, 'j.score', '>=', 'round' ],
    'score_end' => [ null, 'j.score', '<=', 'round' ],
    'status' => [ null, 'j.status', '=', null ],
    'task' => [ null, 'j.task_id', '=', null ],
    'task_security' => [ 'Task::getValidSecurity', 't.security', '=', 'task' ],
    'time_begin' => [ 'strtotime', 'j.submit_time', '>=', null ],
    'time_end' => [ 'strtotime', 'j.submit_time', '<=', null ],
    'user' => [ 'User::getIdFromUsername', 'j.user_id', '=', null ],
  ];

  const METHODS = [
    '=' => 'where',
    '>=' => 'where_gte',
    '<=' => 'where_lte',
    'like' => 'where_like',
  ];

  function getRound(): ?Round {
    return Round::get_by_id($this->round_id) ?: null;
  }

  function getTask(): ?Task {
    return Task::get_by_id($this->task_id);
  }

  function getUser(): ?User {
    return User::get_by_id($this->user_id);
  }

  function getSizeString(): string {
    $len = strlen($this->file_contents);
    return sprintf('%.2f kb', $len / 1024);
  }

  function getStatusMessage(): string {
    switch ($this->status) {
      case 'skipped': return 'job ignorat';
      case 'waiting': return 'în așteptare';
      case 'processing': return 'în curs de evaluare';
    }

    // 'done'
    $msg = $this->eval_message;

    if ($this->isScoreViewable()) {
      return sprintf('%s: %s puncte', $msg, $this->score);
    } else if ($this->isPartialFeedbackViewable()) {
      return sprintf('%s: rezultate parțiale disponibile', $msg);
    } else {
      return $msg;
    }
  }

  static function getRangeWithFilters(array $filters, int $offset, int $limit): array {
    $query = self::buildQueryWithFilters($filters);
    return $query
      ->order_by_desc('id')
      ->offset($offset)
      ->limit($limit)
      ->find_many();
  }

  static function getAllWithFilters(array $filters): array {
    $query = self::buildQueryWithFilters($filters);
    return $query->find_many();
  }

  static function countWithFilters(array $filters) {
    $query = self::buildQueryWithFilters($filters);
    return $query->count();
  }

  private static function buildQueryWithFilters(array $filters): ORM {
    $joins = [];
    $query = Model::factory('job')
      ->table_alias('j')
      ->select('j.*');

    foreach ($filters as $key => $value) {
      if (isset(self::FILTERS[$key])) {
        list($preprocessor, $field, $op, $join) = self::FILTERS[$key];
        if ($preprocessor) {
          $value = call_user_func($preprocessor, $value);
        }
        if ($value) {
          $method = self::METHODS[$op];
          $query = $query->$method($field, $value);
          if ($join) {
            $joins[$join] = true;
          }
        }
      }
    }

    $query = self::addJoins($query, $joins);
    return $query;
  }

  private static function addJoins(ORM $query, array $joins): ORM {
    foreach ($joins as $join => $ignored) {
      switch ($join) {
        case 'task':
          $query = $query->join('ia_task', [ 'j.task_id', '=', 't.id' ], 't');
          break;
        case 'round':
          $query = $query->raw_join(
            'join ia_round',
            '(j.round_id = r.id) and (r.public_eval)',
            'r');
          break;
      }
    }

    return $query;
  }

  private static function prependPercent(string $str): string {
    return '%' . $str;
  }

  // Returns true iff the current user owns the job.
  function isOwner(): bool {
    return User::getCurrentId() == $this->user_id;
  }

  function isPartialFeedbackViewable(): bool {
    $task = $this->getTask();
    if (!$task->isViewable() || !$task->public_tests) {
      return false;
    }

    return
      $this->isOwner() ||
      User::isAdmin() ||
      User::isIntern();
  }

  function isScoreViewable(): bool {
    $task = $this->getTask();
    if (!$task->isViewable()) {
      return false;
    }

    $round = $this->getRound();
    if ($round && $round->public_eval) {
      return true;
    }

    return
      $task->isOwner() ||
      User::isAdmin() ||
      User::isIntern();
  }

  function isSourceViewable(): bool {
    if (User::isAdmin()) {
      return true;
    }

    if ($this->isOwner()) {
      return true;
    }

    $task = $this->getTask();
    if ($task->isOwner()) {
      return true;
    }

    if ($task->isPrivate()) {
      return false;
    }

    $incompleteRounds = $task->getIncompleteRounds();
    if (count($incompleteRounds)) {
      return false;
    }

    if ($task->open_source) {
      return true;
    }

    return false;
  }

}
