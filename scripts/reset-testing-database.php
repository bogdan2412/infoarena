<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../common/log.php';

if (!Config::DEVELOPMENT_MODE || !Config::TESTING_MODE) {
  print "To reset the testing database, please set DEVELOPMENT_MODE = true " .
    "and TESTING_MODE = true in Config.php\n";
  exit;
}

require_once __DIR__ . '/../common/round.php';
require_once __DIR__ . '/../www/config.php';
require_once __DIR__ . '/../www/identity.php';
require_once __DIR__ . '/../lib/Core.php';

DB::dropAndRecreateTestDatabase();
db_connect();

$injector = new DataInjector();
$injector->run();

class DataInjector {
  const IP_ADDRESS = '42.42.42.42';

  private array $admin, $intern, $helper, $normal;

  function run(): void {
    $this->createAttachmentDir();
    $this->createPages();
    $this->createTemplates();
    $this->createUsers();
    $this->createTasks();
    $this->createRounds();
    $this->createAttachments();
  }

  private function createAttachmentDir(): void {
    $path = Attachment::getDirectory();
    exec("rm -rf $path");
    $oldUmask = umask(0);
    mkdir($path);
    umask($oldUmask);
  }

  private function createPages(): void {
    $wildcard = __DIR__ . '/../tests/pages/*.textile';
    $files = glob($wildcard);
    foreach ($files as $file) {
      $this->createPageFromFile($file, '');
    }

    $this->createSecurityPages();
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
      textblock_add_revision($name, $title, $revContents, $userId, $security, $timestamp,
                             null, self::IP_ADDRESS);
    }
  }

  private function createSecurityPages(): void {
    foreach ([ 'private', 'protected', 'public' ] as $security) {
      $this->createPage(
        "page-{$security}",
        "page-{$security}",
        "Contents of the page-{$security} page.",
        1,
        $security,
        5);
    }
  }

  private function secondsAgo(int $numSeconds): string {
    return $this->getRelativeDate("PT{$numSeconds}S", false);
  }

  private function daysAgo(int $numDays): string {
    return $this->getRelativeDate("P{$numDays}D", false);
  }

  private function daysInTheFuture(int $numDays): string {
    return $this->getRelativeDate("P{$numDays}D", true);
  }

  private function getRelativeDate(string $durationString, bool $add): string {
    $date = new DateTime();
    $interval = new DateInterval($durationString);
    if ($add) {
      $date->add($interval);
    } else {
      $date->sub($interval);
    }
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

  private function createRounds(): void {
    $this->createArchiveRound();
    $this->createClassicRound();
    $this->createUserRound();
  }

  private function createArchiveRound(): void {
    printf("* Creating archive round round-archive\n");
    $round = [
      'id' => 'round-archive',
      'type' => 'archive',
      'title' => 'round-archive',
      'page_name' => 'runda/round-archive',
      'state' => 'running',
      'start_time' => $this->daysAgo(1),
      'public_eval' => 1,
      'user_id' => $this->admin['id'],
    ];
    $params = [
      'duration' => 1000,
    ];
    round_create($round, $params, $this->admin['id']);
    round_update_task_list('round-archive', [], [ 'task1', 'task2' ]);
  }

  private function createClassicRound(): void {
    printf("* Creating classic round round-classic\n");
    $round = [
      'id' => 'round-classic',
      'type' => 'classic',
      'title' => 'round-classic',
      'page_name' => 'runda/round-classic',
      'state' => 'waiting',
      'start_time' => $this->daysInTheFuture(1),
      'public_eval' => 1,
      'user_id' => $this->admin['id'],
    ];
    $params = [
      'duration' => 3,
      'rating_update' => true,
    ];
    round_create($round, $params, $this->admin['id']);
    round_update_task_list('round-classic', [], [ 'task1', 'task2' ]);
  }

  private function createUserRound(): void {
    printf("* Creating user round round-user\n");
    $round = [
      'id' => 'round-user',
      'type' => 'user-defined',
      'title' => 'round-user',
      'page_name' => 'runda/round-user',
      'state' => 'waiting',
      'start_time' => $this->daysInTheFuture(1),
      'public_eval' => 1,
      'user_id' => $this->normal['id'],
    ];
    $params = [
      'duration' => 3,
      'rating_update' => true,
    ];
    round_create($round, $params, $this->normal['id']);
    round_update_task_list('round-user', [], [ 'task1' ]);
  }

  private function createAttachments(): void {
    $this->createAttachment('file1.txt', 'page-public');
    $this->createAttachment('file1.txt', 'page-protected');
    $this->createAttachment('file1.txt', 'page-private');
  }

  private function createAttachment(string $name, string $pageName) {
    printf("* Creating attachment %s of page %s\n", $name, $pageName);
    $src = __DIR__ . '/../tests/attachments/' . $name;
    $size = filesize($src);
    attachment_insert($name, $size, 'text/plain', $pageName,
                      $this->admin['id'], self::IP_ADDRESS);

    $attachment = attachment_get($name, $pageName);
    $dest = attachment_get_filepath($attachment);
    copy($src, $dest);
  }

}
