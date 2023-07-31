<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../common/log.php';

if (!Config::DEVELOPMENT_MODE || !Config::TESTING_MODE) {
  print "To reset the testing database, please set DEVELOPMENT_MODE = true " .
    "and TESTING_MODE = true in Config.php\n";
  exit;
}

require_once __DIR__ . '/../www/config.php';
require_once __DIR__ . '/../www/identity.php';
require_once __DIR__ . '/../lib/Core.php';

DB::dropAndRecreateTestDatabase();
db_connect();

$injector = new DataInjector();
$injector->run();

class DataInjector {

  private array $admin, $intern, $helper, $normal;

  function run(): void {
    $this->createPages();
    $this->createTemplates();
    $this->createUsers();
    $this->createTasks();
  }

  private function createPages(): void {
    $wildcard = __DIR__ . '/../tests/pages/*.textile';
    $files = glob($wildcard);
    foreach ($files as $file) {
      $this->createPageFromFile($file, '');
    }
  }

  private function createTemplates(): void {
    $wildcard = __DIR__ . '/../tests/templates/*.textile';
    $files = glob($wildcard);
    foreach ($files as $file) {
      $this->createPageFromFile($file, 'template/');
    }
  }

  private function createPageFromFile(string $filename, string $prefix): void {
    preg_match('|/([^/]+)\.textile$|', $filename, $match);
    $name = $match[1];
    $name = str_replace('_slash_', '/', $name);
    $name = $prefix . $name;

    $lines = file($filename, FILE_IGNORE_NEW_LINES);
    $title = $lines[0];
    $rest = array_slice($lines, 2);
    $contents = implode("\n", $rest);
    printf("* Creating template %s (%s) from file...\n", $name, $title);

    $this->createAdminPage($name, $title, $contents);
  }

  private function createAdminPage(string $name, string $title, string $contents): void {
    $this->createPage($name, $title, $contents, 1, 'public', 5);
  }

  // Note: When creating several revisions in burst, we need to space out the
  // timestamps because the revision table uses (name, timestamp) as the primary
  // key.
  private function createPage(string $name, string $title, string $contents,
                      int $userId, string $security, int $numRevisions): void {
    printf("* Creating page %s (%s)\n", $name, $title);
    for ($i = 1; $i <= $numRevisions; $i++) {
      $timestamp = $this->secondsAgo($numRevisions - $i);
      $revContents = $contents . "\n\nThis is revision $i of $name.";
      textblock_add_revision($name, $title, $revContents, $userId, $security, $timestamp);
    }
  }

  private function secondsAgo(int $numSeconds): string {
    $date = new DateTime();
    $durationString = sprintf('PT%sS', $numSeconds);
    $date->sub(new DateInterval($durationString));
    return $date->format('Y-m-d H:i:s');
  }

  private function createUsers(): void {
    $this->admin = $this->createUser('admin', 'Admin Admin', '1234', 'admin');
    $this->helper = $this->createUser('helper', 'Helper Helper', '1234', 'helper');
    $this->intern = $this->createUser('intern', 'Intern Intern', '1234', 'intern');
    $this->normal = $this->createUser('normal', 'Normal Normal', '1234', 'normal');
  }

  private function createUser(
    string $username, string $name, string $password, string $security): array {

    printf("* Creating user %s (%s)\n", $username, $name);

    $user = [
      'id' => 0,
      'username' => $username,
      'full_name' => $name,
      'password' => user_hash_password($password, $username),
      'email' => sprintf('%s@example.com', $username),
      'security_level' => $security,
      'rating_cache' => 0,
    ];

    $user = user_create($user);
    return $user;
  }

  private function createTasks(): void {
    $this->createAdminOpenTask();
    $this->createHelperClosedTask();
  }

  private function createAdminOpenTask(): void {
    printf("* Creating task task1\n");
    $task = [
      'id' => 'task1',
      'user_id' => $this->admin['id'],
      'source' => 'ad-hoc',
      'security' => 'public',
      'title' => 'Task 1',
      'page_name' => 'problema/task1',
      'type' => 'classic',
      'open_source' => true,
      'open_tests' => true,
      'test_count' => 5,
      'test_groups' => '1;2;3;4;5',
      'public_tests' => '1,2',
      'evaluator' => '',
      'use_ok_files' => true,
      'rating' => 1,
    ];
    $params = [
      'timelimit' => 0.5,
      'memlimit' => 16384,
    ];
    task_create($task, $params);
  }

  private function createHelperClosedTask(): void {
    printf("* Creating task task2\n");
    $task = [
      'id' => 'task2',
      'user_id' => $this->helper['id'],
      'source' => 'ad-hoc',
      'security' => 'private',
      'title' => 'Task 2',
      'page_name' => 'problema/task2',
      'type' => 'classic',
      'open_source' => false,
      'open_tests' => false,
      'test_count' => 5,
      'test_groups' => '1;2;3;4;5',
      'public_tests' => '1,2',
      'evaluator' => '',
      'use_ok_files' => true,
      'rating' => 1,
    ];
    $params = [
      'timelimit' => 0.5,
      'memlimit' => 16384,
    ];
    task_create($task, $params);
  }
}
