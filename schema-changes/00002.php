<?php

// We cannot do this in a SQL patch because we don't know the DB name.
$query = sprintf('alter database %s charset utf8mb4 collate utf8mb4_general_ci',
                 Config::DB_NAME);
db_query($query);
