#!/usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR . 'common/db/db.php');

db_connect();

$result = db_fetch('DESCRIBE `ia_round` `type`');
if (getattr($result, 'Type') == "enum('classic','archive'," .
                                "'user-defined','penalty-round')") {
    db_query("ALTER TABLE `ia_round` modify `type` enum('classic',
                                                        'archive',
                                                        'user-defined',
                                                        'penalty-round',
                                                        'acm-round')");
}

$result = db_query_value('SHOW TABLES LIKE "ia_acm_round"');
if ($result == 'ia_acm_round')
    exit(0);

$query = 'CREATE TABLE ia_acm_round(user_id int(11) NOT NULL,
                                    round_id varchar(64) NOT NULL,
                                    task_id varchar(64) NOT NULL,
                                    score int,
                                    penalty int,
                                    submission int,
                                    partial_score int,
                                    partial_penalty int,
                                    partial_submission int,
                                    PRIMARY KEY(user_id, round_id, task_id))';
db_query($query);

$query = 'CREATE INDEX round_id ON ia_acm_round(round_id)';
db_query($query);
