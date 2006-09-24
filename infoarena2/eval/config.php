<?php

require_once('../config.php');

// The directory used as the jail.
define("IA_EVAL_DIR", IA_ROOT . 'eval/');

// The directory with the grader files.
define("IA_GRADER_DIR", IA_ROOT . 'graders/');

// Path to the jrun executable
define("IA_JRUN_PATH", IA_ROOT . 'jrun/jrun');

// The directory used as the jail.
define("IA_EVAL_TEMP_DIR", IA_EVAL_DIR . 'temp/');

// The directory used as the jail.
define("IA_EVAL_JAIL_DIR", IA_EVAL_DIR . 'jail/');

// The user to run unsafe code as. This defaults to nobody.
define("IA_EVAL_JAIL_UID", 65534);

// The group to run unsafe code as. This defaults to nobody.
define("IA_EVAL_JAIL_GID", 65534);

// Niceness to run the unsafe code. 0 disables.
define("IA_EVAL_JAIL_NICE", 0);

// Add log timestamps.
define("LOG_TIMESTAMP_FORMAT", "D-M-Y H:i:s");

?>
