#! /usr/bin/env php
<?php

echo "********************************\n";
echo "*** IMPORT SCRIPT 2.0 (beta) ***\n";
echo "********************************\n";

require_once("../config.php");
require_once("../common/db/db.php");

log_assert(!isset($dbOldLink));
$dbOldLink = mysql_connect(DB_HOST, DB_USER, DB_PASS, true) or log_die('IMPORT: Cannot connect to database.');
mysql_select_db("infoarena1", $dbOldLink) or log_die('IMPORT: Cannot select database.');

db_query("TRUNCATE TABLE ia_round");
db_query("TRUNCATE TABLE ia_task");
db_query("TRUNCATE TABLE ia_round_task");
db_query("DELETE FROM ia_textblock WHERE NOT (`name` LIKE 'template/%') AND `name` != 'Home'");

$result = mysql_query("SELECT * FROM contests", $dbOldLink);
if (!$result) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink).'\n');
        log_die();
}

while ($contest = mysql_fetch_assoc($result)) {
    printf("Adding round \"".$task['ID']."\" ...\n");

    $round_id = $contest['ID'];
    $round_type = "classic";
    $round_user_id = 0;
    $round_active = false;
    round_create($round_id, $round_type, $round_user_id, $round_active);

    /*
        TO DO:
        insert round parameters into database
    */

    $textblock_content = "";
    $textblock_content .= "==Include(page=\"template/roundheader\" round_id=\"$round_id\")==\n\n";
    $textblock_content .= "p. %{color:red}Reverse% %{color:blue}textile% %{color:green}here.%\n\n";
    $textblock_content .= "==Include(page=\"template/roundfooter\" round_id=\"$round_id\")==\n\n";
    $template = textblock_get_revision('template/new_round');
    textblock_add_revision("round/".$round_id, $contest['name'], $textblock_content, 0);

    $task_list = mysql_query("SELECT * FROM tasktable_".$contest['ID']);
    $tasks = array();
    while ($task = mysql_fetch_assoc($task_list)) {
        $tasks[] = $task['ID'];
    }    
    round_update_task_list($round_id, $tasks);
    printf("DONE \"".$contest['ID']."\"!\n");
}

printf("###\n");

$task_list = mysql_query("SELECT * FROM tasktable_arhiva");
while ($task = mysql_fetch_assoc($task_list)) {
    printf("Adding task \"".$task['ID']."\" ...\n");
    
    $task_id = $task['ID'];
    $task_type = "classic" ;
    $task_hidden = false;
    $task_author = $task['autor'];
    $task_source = "info-arena 1.0";
    $task_user_id = 0;
    task_create($task_id, $task_type, $task_hidden, $task_author, $task_source, $task_user_id);

    $parameters['evaluator'] = "eval.c"; // autodetect here
    $parameters['tests'] = $task['evalsteps'];
    $parameters['timelimit'] = $task['timelimit'];
    $parameters['memlimit'] = 65536;
    $parameters['unique_output'] = false;
    $parameters['okfiles'] = true;
    task_update_parameters($task_id, $parameters);  

    $textblock_content = "";
    $textblock_content .= "==Include(page=\"template/taskheader\" task_id=\"$task_id\")==\n\n";
    $textblock_content .= "p. %{color:red}Reverse% %{color:blue}textile% %{color:green}here.%\n\n";
    $textblock_content .= "==Include(page=\"template/taskfooter\" task_id=\"$task_id\")==\n\n";
    textblock_add_revision("task/".$task_id, $task['name'], $textblock_content, 0);
}

?>
