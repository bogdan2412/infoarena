#! /usr/bin/env php
<?php

echo "********************************\n";
echo "*** IMPORT SCRIPT 2.0 (beta) ***\n";
echo "********************************\n";

require_once("../config.php");
require_once("../common/db/db.php");
// For IA_ATTACH_DIR
require_once("../www/config.php");

function read_line($caption = "") {
    echo $caption;
    $r = trim(fgets(STDIN));
    return $r;
}

// Same as read_line, but returns true/false
function read_question($caption = "") {
    while (true) {
        $answer = read_line($caption);
        if (preg_match("/t|true|y|yes|d|da/i", $answer)) {
            return true;
        }
        if (preg_match("/f|false|n|no|nu/i", $answer)) {
            return false;
        }
        echo "Raspunde da/nu/true/false/etc. Ceva sa inteleg si eu.";
    }
}

// Attach a file(from the disk) to a certain page as attachment $attname.
function magic_file_attach($page, $attname, $file)
{
    if (!file_exists($file)) {
        log_error("File to attach not found");
    }
    $att = attachment_get($attname, $page);
    if ($att) {
        $id = $att['id'];
        attachment_update($id, $attname, filesize($file), "text/plain", $page, 0);
        log_print("Updating attachment $attname to $page");
    } else {
        $id = attachment_insert($attname, filesize($file), "text/plain", $page, 0);
        log_print("New attachment $attname to $page");
    }
    if (!copy($file, IA_ATTACH_DIR . $id)) {
        log_error("Failed to copy file to attachment dir");
    }
}

// Magic function to convert a file to a textile block.
// Returns textile string.
function magic_convert_textile($filename) {
    if (!file_exists($filename)) {
        log_error("File $filename to attach not found");
    }
    ob_start();
    system("elinks -dump 1 -dump-width 2048 $filename");
    $ret = ob_get_contents();
    ob_end_clean();
    $lines = explode("\n", $ret);
    foreach ($lines as &$value) {
        // remove excesive whitespace
        $value = preg_replace('/\s\s+/', ' ', $value);
        // remove leading and trailing special characters
        $value = trim($value, " \t\n\r\0\x0B\x00..\x1F\x7F..\xFF");
        // 
    }
    return implode("\n", $lines);
}

// magic_convert_textile for tasks,
function magic_convert_task($task_id) {
    global $ia1_path;
    $fname = $ia1_path . "www/infoarena/docs/arhiva/$task_id/enunt.html";
    $ret = magic_convert_textile($fname);
    $ret = preg_replace("/^\s*Cerin.{1,5}a/mi", "\nh2. Cerinta", $ret);
    $ret = preg_replace("/^\s*date de intrare/mi", "\nh2. Date de Intrare", $ret);
    $ret = preg_replace("/^\s*date de ie.{1,5}ire/mi", "\nh2. Date de Iesire", $ret);
    $ret = preg_replace("/^\s*restric.{1,5}ii/mi", "\nh2. Restrictii", $ret);
    $ret = preg_replace("/^\s*exemplu/mi", "\nh2. Exemplu", $ret);
    $lines = explode("\n", $ret);
    foreach ($lines as &$line) {
        $line = preg_replace('/(\+|\x7C|)-+(\+|\x7C|)/', '', $line);
        $line = str_replace("=<", "<=", $line);
    }
    return implode("\n", $lines);
}

function magic_convert_article($article_id) {
}

// Ask infoarena 1.0 path.
if (1 < $argc) {
    $ia1_path = $argv[1];
} else {
    $ia1_path = read_line("Calea catre info-arena 1.0:\n");
}

//
// Connect to database.
//
log_assert(!isset($dbOldLink));
$dbOldLink = mysql_connect(DB_HOST, DB_USER, DB_PASS, true) or log_die('IMPORT: Cannot connect to database.');
mysql_select_db("infoarena1", $dbOldLink) or log_die('IMPORT: Cannot select database.');

if (read_question("Delete wiki? ")) {
    db_query("TRUNCATE TABLE ia_file");
    db_query("TRUNCATE TABLE ia_textblock_revision");
    db_query("DELETE FROM ia_textblock
                WHERE NOT (`name` LIKE 'template/%') AND
                      NOT (`name` LIKE 'sandbox/%') AND `name` != 'Home'");
}

//
// Import users.
//
if (read_question("Import users? ")) {
    $query = "SELECT * FROM devnet_users";

    db_query("TRUNCATE TABLE ia_user");
    $result = mysql_query("SELECT * FROM devnet_users", $dbOldLink);
    if (!$result) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }

    while ($row = mysql_fetch_assoc($result)) {
        // fill the user data
        $data = array();
        $data['username'] = $row['id'];
        $data['password'] = $row['password'];
        $data['email'] = $row['email'];
        $data['full_name'] = $row['name'];
        $data['newsletter'] = $row['receiveNewsletter'];
        if ($row['admin']) {
            $data['security_level'] = "admin";
        } else {
            $data['security_level'] = "normal";
        }

        // run query
        $res = user_create($data);
        if (!$res) {
            log_error("Failed creating user $data[username]");
        }
    }
}

//
//  Import rounds.
//
if (read_question("Import rounds? ")) {
    log_print("Importing rounds...");
    db_query("TRUNCATE TABLE ia_round");
    db_query("TRUNCATE TABLE ia_round_task");

    // DB query.
    $result = mysql_query("SELECT * FROM contests", $dbOldLink);
    if (!$result) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($contest = mysql_fetch_assoc($result)) {
        log_print("Adding round \"".$contest['ID']."\" ...");

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
        $textblock_content .= "p. %{color:red}Reverse% %{color:blue}textile% %{color:green}here.%\n";
        $textblock_content .= "==Include(page=\"template/roundfooter\" round_id=\"$round_id\")==";
        $template = textblock_get_revision('template/new_round');
        textblock_add_revision("round/".$round_id, $contest['name'], $textblock_content, 0);

        $task_list = mysql_query("SELECT * FROM tasktable_".$contest['ID']);
        $tasks = array();
        while ($task = mysql_fetch_assoc($task_list)) {
            $tasks[] = $task['ID'];
        }    
        round_update_task_list($round_id, $tasks);
        log_print("DONE ".$contest['ID']."\n");
    }
}

//
//  Import scores
//
if (read_question("Import scores? ")) {
    log_print("Importing scores... might take a while.");

    // Prevents adding missing contests
    // FIXME: user.
    db_query("TRUNCATE TABLE ia_score");
    $query = "
            SELECT * FROM `scores`
            JOIN contests on contests.ID = contestID";
    $score_list = mysql_query($query, $dbOldLink);
    if (!$score_list) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($score = mysql_fetch_assoc($score_list)) {
        $rid = $score['contestID'];
        $tid = $score['taskID'];
        // FIXME: USERS.
        $user = user_get_by_username($score['userID']);
        $uid = $user['id'];

        score_update("score", $uid, $tid, $rid, $score['score']);
        score_update("submit-count", $uid, $tid, $rid, $score['score']);
    }
}

//
//  Import tasks
//
if (read_question("Import tasks? ")) {
    db_query("TRUNCATE TABLE ia_task");
    $import_eval = read_question("Import task evaluators? ");
    $import_tests = read_question("Import task tests? ");

    $task_list = mysql_query("SELECT * FROM tasktable_arhiva", $dbOldLink);
    if (!$task_list) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($task = mysql_fetch_assoc($task_list)) {
        log_print("Adding task \"".$task['ID']."\" ...");

        // Basic shit.
        $task_id = $task['ID'];
        $task_type = "classic" ;
        $task_hidden = false;
        $task_author = $task['autor'];
        $task_source = "info-arena 1.0";
        $task_user_id = 0;
        task_create($task_id, $task_type, $task_hidden, $task_author, $task_source, $task_user_id);
        
        // Basic content.
        $textblock_content = "";
        $textblock_content .= "==Include(page=\"template/taskheader\" task_id=\"$task_id\")==\n\n";
        $textblock_content .= magic_convert_task($task_id);
        $textblock_content .= "==Include(page=\"template/taskfooter\" task_id=\"$task_id\")==";
        textblock_add_revision("task/".$task_id, $task['name'], $textblock_content, 0);

        $parameters = array();

        // Handle evaluator.
        $parameters['evaluator'] = "B0RK";
        if ($import_eval) {
            $task_dir = $ia1_path . "eval/arhiva/$task_id/";
            if (file_exists($task_dir . "eval.c")) {
                $parameters['evaluator'] = "eval.c";
                magic_file_attach("task/$task_id", "grader_eval.c", $task_dir . "eval.c");
                log_print("Found C evaluator.");
            } else if (file_exists($task_dir . "eval.cpp")) {
                $parameters['evaluator'] = "eval.cpp";
                magic_file_attach("task/$task_id", "grader_eval.cpp", $task_dir . "eval.cpp");
                log_print("Found C++ evaluator.");
            } else if (file_exists($task_dir . "eval.pas")) {
                $parameters['evaluator'] = "eval.pas";
                magic_file_attach("task/$task_id", "grader_eval.pas", $task_dir . "eval.pas");
                log_print("Found Pascal evaluator.");
            } else if (file_exists($task_dir . "eval")) {
                // FIXME: check unique_input/output
                // FIXME: check #!
                $parameters['evaluator'] = "eval.sh";
                magic_file_attach("task/$task_id", "grader_eval.sh", $task_dir . "eval");
                log_print("Fall back to shell evaluator.");
            } else {
                log_error("Evaluator missing, wtf?");
            }
        }

        // Tests. Yay. HACK: Also determines unique_output.
        $parameters['okfiles'] = true;
        if ($import_tests) {
            for ($tid = 1; $tid <= $task['evalsteps']; ++$tid) {
                // Attach input.
                magic_file_attach("task/$task_id", "grader_test$tid.in", $task_dir . "test$tid.in");

                // Attach ok file.
                if (!file_exists($task_dir . "test$tid.ok")) {
                    $parameters['okfiles'] = false;
                    log_warn("TASK $task_id HAS NO OK FILES");
                } else {
                    magic_file_attach("task/$task_id", "grader_test$tid.ok", $task_dir . "test$tid.ok");
                }
            }
        }

        $parameters['tests'] = $task['evalsteps'];
        $parameters['timelimit'] = $task['timelimit'];
        $parameters['memlimit'] = 65536;
        $parameters['unique_output'] = false;
        $parameters['okfiles'] = false;
        // Update parameters.
        task_update_parameters($task_id, $parameters);  
        log_print("DONE $task_id\n");
    }
}

if (read_question('Import articles? ')) {
    // hash article categories
    $art_cat = mysql_query("SELECT * FROM info_artcat", $dbOldLink);
    if (!$art_cat){
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($cat = mysql_fetch_assoc($art_cat)) {
        $category[$cat['id']] = $cat['caption'];
    }

    // get articles    
    $articles = mysql_query("SELECT * FROM info_art", $dbOldLink);
    if (!$articles) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($article = mysql_fetch_assoc($articles)) {
        log_print("Adding article \"".$article['title']."\" ...");

        $textblock_content = 'h2. '.$article['title'].'\n';
//        $textblock_content..= 'Creat la data de '.$article['postdate'].', cateogoria '.$catergory[$article['catId']].'\n';
        //$textblock_content .= '
    }
        
};
    
?>
