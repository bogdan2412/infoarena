#!/usr/bin/env php
<?php

require_once dirname($argv[0]).'/utilities.php';
require_once IA_ROOT_DIR.'common/db/db.php';

db_connect();

$result = db_query_value('SHOW TABLES LIKE "ia_task_view_sources"');
if ($result != 'ia_task_view_sources') {
    $query = 'CREATE TABLE ia_task_view_sources(user_id int(11) NOT NULL,
                                                task_id varchar(64) NOT NULL,
                                                first_request datetime NOT NULL,
                                                PRIMARY KEY(user_id, task_id))';
    db_query($query);
}

$query = sprintf(
    'SHOW INDEXES FROM ia_score_user_round_task
     WHERE Key_name = %s',
    db_quote('task_score'));
$result = db_fetch($query);
if ($result === null) {
    $query = 'CREATE INDEX task_score
              ON ia_score_user_round_task(task_id, score)
              USING BTREE';
    db_query($query);
}

$query = sprintf(
    'SHOW INDEXES FROM ia_score_user_round_task
    WHERE Key_name = %s',
    db_quote('user_task_score'));
$result = db_fetch($query);
if ($result === null) {
    $query = 'CREATE INDEX user_task_score
              ON ia_score_user_round_task(user_id, task_id, score)
              USING BTREE';
    db_query($query);
}
