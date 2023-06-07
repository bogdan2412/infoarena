<?php

require_once(IA_ROOT_DIR . 'common/log.php');
require_once(IA_ROOT_DIR . 'common/db/job.php');
require_once(IA_ROOT_DIR . 'common/db/task_statistics.php');

class Database {
  const ADMIN_USERNAMES = [ 'francu', 'Catalin.Francu', 'mihai.tutu' ];

  private array $admins;
  private array $adminIds;

  function __construct() {
    db_connect();
  }

  function loadAdmins() {
    $userNames = implode(', ', self::ADMIN_USERNAMES);
    Log::info('Loading %d admin users: %s.', [ count(self::ADMIN_USERNAMES), $userNames ]);

    $this->admins = [];
    foreach (self::ADMIN_USERNAMES as $username) {
      $user = user_get_by_username($username);
      $user or fatal('Admin "%s" not found.', $username);
      $this->admins[$user['id']] = $user;
    }
    $this->adminIds = array_keys($this->admins);
  }

  function loadTasks(): array {
    $tasks = task_get_all();

    usort($tasks, function($a, $b) {
      return $a['id'] <=> $b['id'];
    });

    return $tasks;
  }

  function loadTaskById($id): array {
    $task = task_get($id);
    if (!$task) {
      throw new BException('Task %s not found.', [ $id ]);
    }
    return $task;
  }

  function getTaskTimeLimit($taskId) {
    $taskParams = task_get_parameters($taskId);
    $timeLimit = (float)$taskParams['timelimit'];
    return $timeLimit;
  }

  function loadJobs($taskId): array {
    return job_get_by_task_id_status($taskId, 'done');
  }

  function filterAdminJobs(array $jobs): array {
    $adminJobs = [];
    foreach ($jobs as $j) {
      if (in_array($j['user_id'], $this->adminIds)) {
        $adminJobs[] = $j;
      }
    }
    return $adminJobs;
  }
}
