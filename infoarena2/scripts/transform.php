#! /usr/bin/env php
<?php

echo "***********************************\n";
echo "*** TRANSFORM SCRIPT 2.0 (beta) ***\n";
echo "***********************************\n";

require_once("../config.php");
require_once("../common/db/db.php");

function read_line($caption = "") {
    echo $caption;
    $r = trim(fgets(STDIN));
    return $r;
}

if (1 < $argc) {
    $ia_path = $argv[1];
}
else {
    $ia_path = read_line("Calea catre info-arena 1.0:\n");
}

function links_magic($task_id, $ia_path) {
    ob_start();
    system("elinks -dump 1 -dump-charset windows-1250 -dump-width 2048 ".$ia_path."/www/infoarena/docs/arhiva/".$task_id."/enunt.html");
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
}

log_assert(!isset($dbOldLink));
$dbOldLink = mysql_connect(DB_HOST, DB_USER, DB_PASS, true) or log_die('TRANSOFRM: Cannot connect to database.');
mysql_select_db("infoarena1", $dbOldLink) or log_die('TRANSFORM: Cannot select database.');

$task_list = mysql_query("SELECT * FROM tasktable_arhiva");
while ($task = mysql_fetch_assoc($task_list)) {
    printf("Transforming task \"".$task['ID']."\" ...\n");
    
    $task_id = $task['ID'];
    $task_type = "classic" ;
    $task_hidden = false;
    $task_author = $task['autor'];
    $task_source = "info-arena 1.0";
    $task_user_id = 0;
    if (!task_get($task_id)) {
        log_die("TRANSFORM: Task ".$task_id." is missing!");
    }

    $textblock_content = "";
    $textblock_content .= "==Include(page=\"template/taskheader\" task_id=\"$task_id\")==\n\n";
    $textblock_content .= links_magic($task_id, $ia_path)."\n";
    $textblock_content .= "==Include(page=\"template/taskfooter\" task_id=\"$task_id\")==\n\n";
    textblock_add_revision("task/".$task_id, $task['name'], $textblock_content, 0);
}

?>
