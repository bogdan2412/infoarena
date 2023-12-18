<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../common/log.php';

if (!Config::DEVELOPMENT_MODE || !Config::TESTING_MODE) {
  print "To reset the testing database, please set DEVELOPMENT_MODE = true " .
    "and TESTING_MODE = true in Config.php\n";
  exit;
}

require_once __DIR__ . '/../common/db/score.php';
require_once __DIR__ . '/../common/round.php';
require_once __DIR__ . '/../www/config.php';
require_once __DIR__ . '/../lib/Core.php';

DB::dropAndRecreateTestDatabase();
db_connect();

$injector = new DataInjector();
$injector->run();

class DataInjector {
  const IP_ADDRESS = '42.42.42.42';
  const NUM_TEST_CASES = 5;

  private array $admin, $intern, $helper, $normal, $normal2;
  private $jobCounter = 0;

  function run(): void {
    $this->createAttachmentDir();
    $this->createPages();
    $this->createTemplates();
    $this->createUsers();
    $this->createTasks();
    $this->createRounds();
    $this->createAttachments();
    $this->createTags();
    $this->createJobs();
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
    $this->normal2 = $this->createUser('normal2', 'Normal2 Normal2', '1234', 'normal');
  }

  private function createUser(
    string $username, string $name, string $password, string $security): array {

    printf("* Creating user %s (%s)\n", $username, $name);

    $user = [
      'id' => 0,
      'username' => $username,
      'full_name' => $name,
      'password' => user_hash_password($password, $username),
      'email' => sprintf('%s@nerdarena.ro', $username),
      'security_level' => $security,
      'rating_cache' => 0,
    ];

    $user = user_create($user);
    return $user;
  }

  private function createTasks(): void {
    $this->createAdminOpenTask();
    $this->createHelperClosedTask();
    $this->createGroupedTestTask();
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
      'test_count' => self::NUM_TEST_CASES,
      'test_groups' => '',
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
      'test_count' => self::NUM_TEST_CASES,
      'test_groups' => '',
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

  private function createGroupedTestTask(): void {
    printf("* Creating task task3\n");
    $task = [
      'id' => 'task3',
      'user_id' => $this->admin['id'],
      'source' => 'ad-hoc',
      'security' => 'public',
      'title' => 'Task 3',
      'page_name' => 'problema/task3',
      'type' => 'classic',
      'open_source' => true,
      'open_tests' => true,
      'test_count' => self::NUM_TEST_CASES,
      'test_groups' => '1-2;3-4;5',
      'public_tests' => '1,3',
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
    $this->createPenaltyRound();
    $this->createRunningClassicRound();
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

  private function createPenaltyRound(): void {
    printf("* Creating penalty round round-penalty\n");
    $round = [
      'id' => 'round-penalty',
      'type' => 'penalty-round',
      'title' => 'round-penalty',
      'page_name' => 'runda/round-penalty',
      'state' => 'running',
      'start_time' => $this->daysAgo(1),
      'public_eval' => 1,
      'user_id' => $this->admin['id'],
    ];
    $params = [
      'duration' => 48,
      'rating_update' => true,
      'decay_period' => 7200, // 1% every 2 hours --> 12% for sources sent now.
      'submit_cost' => 10,
      'minimum_score' => 75,
    ];
    round_create($round, $params, $this->admin['id']);
    round_update_task_list('round-penalty', [], [ 'task1' ]);
  }

  private function createRunningClassicRound(): void {
    printf("* Creating running classic round round-running\n");
    $round = [
      'id' => 'round-running',
      'type' => 'classic',
      'title' => 'round-running',
      'page_name' => 'runda/round-running',
      'state' => 'running',
      'start_time' => $this->daysAgo(1),
      'public_eval' => 0,
      'user_id' => $this->admin['id'],
    ];
    $params = [
      'duration' => 48,
      'rating_update' => true,
    ];
    round_create($round, $params, $this->admin['id']);
    round_update_task_list('round-running', [], [ 'task3' ]);
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
    $this->createAttachment('grader_test1.in', 'problema/task1');
  }

  private function createAttachment(string $name, string $pageName): void {
    printf("* Creating attachment %s of page %s\n", $name, $pageName);
    $src = __DIR__ . '/../tests/attachments/' . $name;
    $size = filesize($src);
    attachment_insert($name, $size, 'text/plain', $pageName,
                      $this->admin['id'], self::IP_ADDRESS);

    $attachment = attachment_get($name, $pageName);
    $dest = attachment_get_filepath($attachment);
    copy($src, $dest);
  }

  private function createTags(): void {
    $category1Id = $this->createTag('category1', 'method', 0);
    $category2Id = $this->createTag('category2', 'method', 0);

    $tag1Id = $this->createTag('tag1', 'algorithm', $category1Id);
    $tag2Id = $this->createTag('tag2', 'algorithm', $category1Id);
    $tag3Id = $this->createTag('tag3', 'algorithm', $category2Id);
    $tag4Id = $this->createTag('tag4', 'algorithm', $category2Id);

    $author1Id = $this->createTag('author1', 'author', 0);

    $this->applyTaskTag($category1Id, 'task1');
    $this->applyTaskTag($category2Id, 'task1');
    $this->applyTaskTag($tag1Id, 'task1');
    $this->applyTaskTag($tag3Id, 'task1');

    $this->applyTaskTag($category1Id, 'task2');
    $this->applyTaskTag($category2Id, 'task2');
    $this->applyTaskTag($tag2Id, 'task2');
    $this->applyTaskTag($tag4Id, 'task2');
  }

  private function createTag(string $name, string $type, int $parent): int {
    printf("* Creating tag [%s] of type [%s]\n", $name, $type);
    $tag = [
      'name' => $name,
      'type' => $type,
      'parent' => $parent,
    ];
    return tag_assign_id($tag);
  }

  private function applyTaskTag(int $tagId, string $taskId): void {
    tag_add('task', $taskId, $tagId);
  }

  private function createJobs(): void {
    $this->createJob($this->admin, 'task1', '', 's1.cpp',
                     'done', 10, 'Evaluare completă');
    $this->createJob($this->admin, 'task2', '', 's2.cpp',
                     'done', 20, 'Evaluare completă');
    $this->createJob($this->admin, 'task1', 'round-classic', 's1.cpp',
                     'done', 30, 'Evaluare completă');
    $this->createJob($this->admin, 'task1', 'round-archive', 's1.cpp',
                     'done', 40, 'Evaluare completă');
    $this->createJob($this->intern, 'task1', 'round-archive', 's2.cpp',
                     'done', 50, 'Evaluare completă');
    $this->createJob($this->helper, 'task1', 'round-archive', 's3.c',
                     'done', 60, 'Evaluare completă');
    $this->createJob($this->normal, 'task1', 'round-archive', 's4.c',
                     'waiting');
    $this->createJob($this->normal, 'task3', 'round-running', 's1.cpp',
                     'done', 70, 'Evaluare completă');
    $this->createJob($this->normal, 'task1', 'round-penalty', 's1.cpp',
                     'done', 80, 'Evaluare completă');
    $this->createJob($this->normal, 'task1', 'round-penalty', 's1.cpp',
                     'done', 80, 'Evaluare completă');
    $this->createJob($this->normal, 'task1', 'round-penalty', 's1.cpp',
                     'done', 80, 'Evaluare completă');
    $this->createJob($this->normal, 'task1', 'round-penalty', 's1.cpp',
                     'done', 80, 'Evaluare completă');
    $this->createJob($this->normal2, 'task1', 'round-archove', 's1.cpp',
                     'done', 100, 'Evaluare completă');
  }

  private function createJob(
    array $user, string $taskId, string $roundId, string $sourceFile,
    string $status, int $score = 0, string $evalMessage = ''): void {

    printf("* Creating job from user [%s] for task [%s], round [%s]\n",
           $user['username'], $taskId, $roundId);

    $path = __DIR__ . '/../tests/sources/' . $sourceFile;
    $extension = explode('.', $sourceFile)[1];

    $j = Model::factory('Job')->create();
    $j->user_id = $user['id'];
    $j->round_id = $roundId;
    $j->task_id = $taskId;
    $j->submit_time = $this->secondsAgo(100 - $this->jobCounter);
    $j->compiler_id = $extension;
    $j->file_contents = file_get_contents($path);
    $j->status = $status;
    if ($status == 'done') {
      $j->score = $score;
      $j->eval_log = "This is the compilation log for {$sourceFile}.";
      $j->eval_message = $evalMessage;
    }
    $j->submissions = Job::countUserRoundTaskSubmissions($user['id'], $roundId, $taskId);
    $j->remote_ip_info = self::IP_ADDRESS;
    $j->save();
    $this->createTests($j);

    if ($status == 'done' && $roundId) {
      score_update($user['id'], $taskId, $roundId, $score);
    }

    $this->jobCounter++;
  }

  private function createTests(Job $job): void {
    $task = $job->getTask();
    $taskTests = new TaskTests($task);
    $groups = $taskTests->getGroups();
    $testNo = 0;

    foreach ($groups as $groupNo => $tests) {
      foreach ($tests as $ignored) {
        $t = Model::factory('JobTest')->create();
        $t->job_id = $job->id;
        $t->test_number = ++$testNo;
        $t->test_group = $groupNo;
        $t->exec_time = 100; // millis
        $t->mem_used = 500; // kilobytes
        $t->points = $job->score / self::NUM_TEST_CASES;
        $t->grader_message = 'Some grader message.';
        $t->save();
      }
    }
  }

}
