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
    $this->createNewUserTemplate();
    $this->createUsers();
    $this->createSimpleTemplate('login');
    $this->createSimpleTemplate('userheader');
    $this->createPage('home', 'Home page', 'This is the home page.', $this->admin['id'], 'public', 5);
  }

  function createNewUserTemplate(): void {
    $this->createPage(
      'template/newuser',
      'Profile of %user_id%',
      'My username is %user_id%. Here is something else about myself.',
      0,
      'public',
      5);
  }

  function createSimpleTemplate(string $name): void {
    $this->createPage(
      "template/{$name}",
      "template/{$name}",
      "This is the {$name} template.",
      $this->admin['id'],
      'public',
      5);
  }

  function createUsers(): void {
    $this->admin = $this->createUser('admin', 'Admin Admin', '1234', 'admin');
    $this->intern = $this->createUser('intern', 'Intern Intern', '1234', 'intern');
    $this->helper = $this->createUser('helper', 'Helper Helper', '1234', 'helper');
    $this->normal = $this->createUser('normal', 'Normal Normal', '1234', 'normal');
  }

  private function createUser(
    string $username, string $name, string $password, string $security): array {

    printf("Creating user %s (%s)\n", $username, $name);

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

  // Note: When creating several revisions in burst, we need to space out the
  // timestamps because the revision table uses (name, timestamp) as the primary
  // key.
  private function createPage(string $name, string $title, string $contents,
                      int $userId, string $security, int $numRevisions): void {
    printf("Creating page %s (%s)\n", $name, $title);
    for ($i = 1; $i <= $numRevisions; $i++) {
      $timestamp = $this->secondsAgo($numRevisions - $i);
      $revContents = $contents . " This is revision $i.";
      textblock_add_revision($name, $title, $revContents, $userId, $security, $timestamp);
    }
  }

  private function secondsAgo(int $numSeconds): string {
    $date = new DateTime();
    $durationString = sprintf('PT%sS', $numSeconds);
    $date->sub(new DateInterval($durationString));
    return $date->format('Y-m-d H:i:s');
  }
}
