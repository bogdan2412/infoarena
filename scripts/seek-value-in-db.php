#! /usr/bin/env php

<?php

require_once(dirname($argv[0]) . "/utilities.php");

db_connect();

$query ="SELECT T.table_name table_name, C.column_name column_name, C.data_type
         FROM information_schema.tables T, information_schema.columns C
         WHERE T.table_name LIKE 'ia_%' AND T.table_name = C.table_name";

$all_columns = db_query($query);

while ($row = db_next_row($all_columns)) {
    $table_name = $row["table_name"];
    $column_name = $row["column_name"];
    $data_type = $row["data_type"];

    if ($data_type == 'varchar') {
        $query = sprintf("SELECT COUNT(*) count
                          FROM %s
                          WHERE `%s` = %s",
                          db_escape($table_name), db_escape($column_name), db_quote($argv[1]));

        $cnt = db_query_value($query);
        if ($cnt) {
            log_print("found " . $argv[1] . " in table " . $table_name . " in column " . $column_name);
        }
    }
}

?>
