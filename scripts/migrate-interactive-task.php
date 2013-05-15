#!/usr/bin/env php
<?php
require_once(dirname($argv[0]) . "/utilities.php");
db_connect();

$query = "ALTER TABLE ia_task
          MODIFY type enum('classic','interactive','output-only')";

db_query($query);
