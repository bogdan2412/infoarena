<?php

require_once __DIR__ . '/third-party/idiorm-1.5.8.php';
require_once __DIR__ . '/third-party/paris-1.5.6.php';

class DB {

  static function init(): void {
    $dsn = sprintf('mysql:host=%s;dbname=%s', Config::DB_HOST, self::getDatabaseName());
    ORM::configure($dsn);
    ORM::configure('username', Config::DB_USER);
    ORM::configure('password', Config::DB_PASSWORD);

    if (Config::LOG_SQL_QUERIES) {
      // This allows var_dump(ORM::get_query_log()) or var_dump(ORM::get_last_query()).
      ORM::configure('logging', true);
    }
  }

  static function getDatabaseName(): string {
    return Config::TESTING_MODE
      ? Config::DB_TEST_NAME
      : Config::DB_NAME;
  }

  static function tableExists(string $tableName): bool {
    $r = ORM::for_table($tableName)
      ->raw_query("show tables like '$tableName'")
      ->find_one();
    return ($r !== false);
  }

  private static function getPasswordArg(): string {
    return Config::DB_PASSWORD
      ? ('-p' . Config::DB_PASSWORD)
      : '';
  }

  static function dropAndRecreateTestDatabase(): void {
    self::dropTestDatabase();
    self::createTestDatabase();
    self::copySchemaToTestDatabase();
  }

  private static function dropTestDatabase(): void {
    $cmd = sprintf(
      '%s -h %s -u %s %s --execute "drop database if exists %s"',
      Config::DB_COMMAND,
      Config::DB_HOST,
      Config::DB_USER,
      self::getPasswordArg(),
      Config::DB_TEST_NAME);
    exec($cmd);
  }

  private static function createTestDatabase(): void {
    $cmd = sprintf(
      '%s -h %s -u %s %s --execute "create database %s"',
      Config::DB_COMMAND,
      Config::DB_HOST,
      Config::DB_USER,
      self::getPasswordArg(),
      Config::DB_TEST_NAME);
    exec($cmd);
  }

  private static function copySchemaToTestDatabase(): void {
    $exportCmd = sprintf(
      '%s -d -h %s -u %s %s %s',
      Config::DB_DUMP_COMMAND,
      Config::DB_HOST,
      Config::DB_USER,
      self::getPasswordArg(),
      Config::DB_NAME);

    $importCmd = sprintf(
      '%s -h %s -u %s %s %s',
      Config::DB_COMMAND,
      Config::DB_HOST,
      Config::DB_USER,
      self::getPasswordArg(),
      Config::DB_TEST_NAME);

    $removeAutoincrementCmd = 'sed "s/ AUTO_INCREMENT=[0-9]*//g"';

    $cmd = "$exportCmd | $removeAutoincrementCmd | $importCmd";
    exec($cmd);
  }
}
