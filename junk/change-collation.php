#! /usr/bin/env php
<?php

// Database host.
define("DB_HOST", 'db');

// Database name.
define("DB_NAME", 'infoarena');

// Database user.
define("DB_USER", 'infoarena');

// Database password
define("DB_PASS", 'sha0lyn');

// New charset/collation for the database.
define("DB_CHARSET", 'latin1');
define("DB_COLLATION", 'latin1_general_ci');

// Don't modify below.

mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

function db_query($command) {
    $res = mysql_query($command);
    if ($res === false) {
        print(mysql_error());
        die();
    }
    return $res;
}

function db_fetch_all($qres) {
    $res = array();
    while (true) {
        $row = mysql_fetch_assoc($qres);
        if ($row === false) {
            return $res;
        }
        $res[] = $row;
    }
}

$table_list = db_fetch_all(db_query("SHOW TABLES"));
foreach ($table_list as $table_list_row) {
    $table = array_values($table_list_row);
    $table = $table[0];

    $esctable = mysql_real_escape_string($table);
    $query = sprintf("ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s",
            $esctable, DB_CHARSET, DB_COLLATION);
    db_query($query);
}

?>
