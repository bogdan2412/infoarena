<?php

/**
 * A script that republicizes tasks that were made protected during rounds.
 */

require_once(__DIR__ . "/../config.php");
require_once(Config::ROOT . "common/db/db.php");
require_once(Config::ROOT . "common/db/task.php");
require_once(Config::ROOT . "common/log.php");

db_connect();

/* Getting all the tasks with protected status and and are also part of completed rounds.
	No other way to create the select query. */
$select_sql_protected = <<<SQL
  SELECT DISTINCT task.task_id as id_problema
  FROM ia_round_task task
  LEFT JOIN ia_round round ON task.round_id = round.id
  LEFT JOIN ia_task problema ON problema.id = task.task_id
  WHERE round.state = "complete"
  AND problema.security = "protected"
SQL;

$result_protected = db_query($select_sql_protected);

/* Getting all the tasks with protected status and and are part of running rounds. Except "arhiva".
	No other way to create the select query. */
$select_sql_running = <<<SQL
  SELECT DISTINCT task.task_id as id_problema_protejat
  FROM ia_round_task task
  LEFT JOIN ia_round round ON task.round_id = round.id
  LEFT JOIN ia_task problema ON problema.id = task.task_id
  WHERE round.state = "running"
  AND problema.security = "protected"
  AND round.id != "arhiva"
SQL;

$result_running = db_query($select_sql_running);

/* Transforming the object from db_query into associative array for search */
$array_protected = array();
foreach ($result_running as $row) {
	$array_protected[] = $row['id_problema_protejat'];
}

/* Searching the array to make sure we do not make public any of the tasks which should stay protected, as part of a running round. */
$message_for_log = '';
foreach ($result_protected as $row) {
	$found = in_array ( $row["id_problema"] , $array_protected , true );
	if (!$found) {
		task_update_security($row["id_problema"], 'public');
		$message_for_log .=  $row["id_problema"] . ' | ';
	}
}

/* Logging a message if any tasks became public. */
if ($message_for_log) {
	$message_for_log .= 'Tasks became public.';
	log_print($message_for_log, false);
    print "{$message_for_log}\n";
}
