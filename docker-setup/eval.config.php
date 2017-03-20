<?php

// link main configuration
require_once dirname($argv[0]).'/../config.php';

// Judge username/password, used via HTTP AUTH basic to download tests and
// graders. Judge has to have admin access.
// Set a strong & secret password when putting this into production.
// Default works with svn.
define('IA_JUDGE_USERNAME', 'eval');
define('IA_JUDGE_PASSWORD', 'eval');

// Poll interval, in miliseconds.
define('IA_JUDGE_POLL_INTERVAL', 100);

// The user to run unsafe code as. This defaults to nobody.
define('IA_JUDGE_JRUN_UID', 65534);

// The group to run unsafe code as. This defaults to nobody.
define('IA_JUDGE_JRUN_GID', 65534);

// Niceness to run the unsafe code. 0 disables.
define('IA_JUDGE_JRUN_NICE', 0);

// Time limit for graders.
define('IA_JUDGE_TASK_EVAL_TIMELIMIT', 5000);

// Memory limit for graders.
define('IA_JUDGE_TASK_EVAL_MEMLIMIT', 65536);

// Time limit for interactive programs.
define('IA_JUDGE_TASK_INTERACT_TIMELIMIT', 10000);

// Memory limit for interactive programs.
define('IA_JUDGE_TASK_INTERACT_MEMLIMIT', 131072);

// Maximum score per task.
define('IA_JUDGE_MAX_SCORE', 100);

// Maximum length allowed for a evaluator's feedback message
define('IA_JUDGE_MAX_EVAL_MESSAGE', 100);

// If true then keep all jails forever.
// This is useful in finding judge bugs.
define('IA_JUDGE_KEEP_JAILS', true);

// Retry downloading grader data.
define('IA_JUDGE_MAX_GRADER_DOWNLOAD_RETRIES', 5);

// Add log timestamps.
// FIXME: horrible hack.
define('IA_LOG_TIMESTAMP_FORMAT', 'Y-m-d H:i:s');

// Python support.
define('IA_JUDGE_PY_DISTRO', '~/external/Python-2.6.1');
define('IA_JUDGE_PY_COMPILER',
       IA_ROOT_DIR.'scripts/pybin.sh '.IA_JUDGE_PY_DISTRO.' '.
       IA_ROOT_DIR.'eval/temp');

?>
