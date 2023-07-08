#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . '/utilities.php');
require_once(Config::ROOT . 'common/db/db.php');

db_connect();

$result = db_fetch('DESCRIBE `ia_job` `status`');
if (getattr($result, 'Type') == "enum('waiting','done','processing')") {
    db_query("ALTER TABLE `ia_job` modify `status` enum('waiting','done',"
            . "'processing','skipped') NOT NULL DEFAULT 'waiting'");
}
