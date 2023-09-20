<?php

class TaskPeep extends Base {

  public static string $_table = 'task_peep';

  static function exists(int $userId, string $taskId): bool {
    $rec = Model::factory('TaskPeep')
      ->where('user_id', $userId)
      ->where('task_id', $taskId)
      ->find_one();

    return ($rec !== false);
  }

  static function add(int $userId, string $taskId): void {
    if (!self::exists($userId, $taskId)) {
      $rec = Model::factory('TaskPeep')->create();
      $rec->user_id = $userId;
      $rec->task_id = $taskId;
      $rec->first_request = Time::formatMillis();
      $rec->save();
    }
  }

}
