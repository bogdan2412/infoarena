#!/usr/bin/env php
<?php
  require_once(dirname($argv[0]) . "/utilities.php");
  require_once(IA_ROOT_DIR . "common/db/task.php");
  require_once(IA_ROOT_DIR . "common/tags.php");
  db_connect();

  $query = "ALTER TABLE ia_user
      MODIFY security_level enum('admin', 'helper', 'intern', 'normal')";

  db_query($query);
?>
