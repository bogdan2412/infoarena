<?php

require_once __DIR__ . '/third-party/idiorm-1.5.8.php';
require_once __DIR__ . '/third-party/paris-1.5.6.php';

class DB {

  static function init(): void {
    $dsn = sprintf('mysql:host=%s;dbname=%s', Config::DB_HOST, Config::DB_NAME);
    ORM::configure($dsn);
    ORM::configure('username', Config::DB_USER);
    ORM::configure('password', Config::DB_PASSWORD);

    if (Config::LOG_SQL_QUERIES) {
      // This allows var_dump(ORM::get_query_log()) or var_dump(ORM::get_last_query()).
      ORM::configure('logging', true);
    }
  }

}
