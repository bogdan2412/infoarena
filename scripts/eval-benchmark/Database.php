<?php

require_once(IA_ROOT_DIR . 'common/log.php');
require_once(IA_ROOT_DIR . 'common/db/job.php');
require_once(IA_ROOT_DIR . 'common/db/task_statistics.php');

class Database {
  const ADMIN_USERNAMES = [ 'francu', 'Catalin.Francu', 'mihai.tutu' ];

  private array $userMap = [];
  private array $adminMap = [];

  function __construct() {
    db_connect();
  }

  function loadUsers(): void {
    $users = user_get_all();
    foreach ($users as $u) {
      $this->userMap[$u['id']] = $u['username'];
      if (in_array($u['username'], self::ADMIN_USERNAMES)) {
        $this->adminMap[$u['id']] = $u['username'];
      }
    }

    $this->logLoadedUsers();
  }

  private function logLoadedUsers(): void {
    $numUsers = count($this->userMap);
    $numAdmins = count($this->adminMap);
    $adminUsernames = implode(', ', $this->adminMap);
    Log::info('Loaded %d users, of which %d admins (%s).',
              [ $numUsers, $numAdmins, $adminUsernames ]);
  }

  function getUser($userId): string {
    return $this->userMap[$userId];
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

  function getTaskParams($taskId): array {
    return task_get_parameters($taskId);
  }

  function loadJobs($taskId): array {
    return job_get_by_task_id_status($taskId, 'done');
  }

  function filterAdminJobs(array $jobs): array {
    $adminJobs = [];
    foreach ($jobs as $j) {
      if (isset($this->adminMap[$j['user_id']])) {
        $adminJobs[] = $j;
      }
    }
    return $adminJobs;
  }

  function loadTests(int $jobId): array {
    return job_test_get_all($jobId);
  }
}
