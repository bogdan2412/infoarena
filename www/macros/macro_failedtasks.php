<?php

require_once(Config::ROOT.'www/macros/macro_solvedtasks.php');

// Display tasks user submitted to but hasn't received maxpoints.
function macro_failedtasks($args) {
    return macro_solvedtasks($args, true);
}

?>
