#! /usr/bin/env php
<?php

require_once("utilities.php");

// Attach a file(from the disk) to a certain page as attachment $attname.
function magic_file_attach($page, $attname, $file)
{
    if (!file_exists($file)) {
        log_error("File to attach not found ($file)");
    }
    if (!is_readable($file)) {
        log_error("File to attach not readable ($file)");
    }

    $mime = get_mime_type($file);
    $att = attachment_get($attname, $page);
    if ($att) {
        $id = $att['id'];
        attachment_update($id, $attname, filesize($file), $mime, $page, 0);
        log_print("Updating attachment $attname to $page mime type $mime.");
    } else {
        $id = attachment_insert($attname, filesize($file), $mime, $page, 0);
        log_print("New attachment $attname to $page mime type $mime.");
        $att = attachment_get($attname, $page);
    }
    if (!copy($file, attachment_get_filepath($att))) {
        log_error("Failed to copy file to attachment dir.");
    }
}

// Magic function to convert a file to a textile block.
// Returns textile string. This is ugly.
function magic_convert_textile($filename, $content = null) {
    if (!file_exists($filename)) {
        if ($content) {
            $filename = tempnam(IA_ATTACH_DIR, "ia");
            $handle = fopen($filename, "w");
            fwrite($handle, $content);
            fclose($handle);
        }
        else log_error("File $filename to convert not found");
    }
    ob_start();
    system("elinks -dump 1 -dump-width 1024 $filename");
    $ret = ob_get_contents();
    ob_end_clean();
    $lines = explode("\n", $ret);
    foreach ($lines as &$value) {
        // remove excesive whitespace
        $value = preg_replace('/\s\s+/', ' ', $value);
        // remove leading and trailing special characters
        $value = trim($value, " \t\n\r\0\x0B\x00..\x1F\x7F..\xFF");
        // more formatting
        $value = preg_replace('/(\+|\x7C)*---+(\+|\x7C)*/', '', $value);
    }
    $content = implode("\n", $lines);
    $content = "==Include(page=\"template/raw\")==\n\n" . $content;
    return $content;
}

// magic_convert_textile for tasks
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
        $line = preg_replace('/(\+|\x7C)*---+(\+|\x7C)*/', '', $line);
        $line = str_replace("=<", "<=", $line);
    }
    return implode("\n", $lines);
}

function magic_title($ugly_title) {
    $ugly_title = preg_replace("/ +/", "_", $ugly_title);
    $ugly_title = preg_replace("/\-+/", "_", $ugly_title);
    $ugly_title = preg_replace("/_+/", "_", $ugly_title);
    $ugly_title = strip_tags($ugly_title);
    return preg_replace("/([^a-z0-9_\-]*)/i", "", $ugly_title);
}

// Ask infoarena 1.0 path.
if (1 < $argc) {
    $ia1_path = $argv[1];
} else {
    $ia1_path = read_line("Unde gasesc info-arena 1.0?");
}

//
// Connect to database.
//
log_assert(!isset($dbOldLink));
$dbOldLink = mysql_connect(DB_HOST, DB_USER, DB_PASS, true) or log_die('IMPORT: Cannot connect to database.');
mysql_select_db("infoarena1", $dbOldLink) or log_die('IMPORT: Cannot select database.');

if (read_bool("Delete wiki?", false)) {
    db_query("TRUNCATE TABLE ia_textblock_revision");
    db_query("DELETE FROM ia_textblock
                WHERE NOT (`name` LIKE 'template/%') AND
                      NOT (`name` LIKE 'sandbox/%') AND `name` != 'Home'");
}

if (read_bool("Delete attachments?", false)) {
    log_warn("Actual files are not deleted.");
    db_query("TRUNCATE TABLE ia_file");
}

//
// Import users.
//
if (read_bool("Import users?", false)) {
    $query = "SELECT * FROM devnet_users";

    $import_avatars = read_bool("Attach old avatars?", false);

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

        if ($import_avatars) {
            $avatar_file = sprintf($ia1_path . 'www/infoarena/gfx/faces/%02d.gif', (int)$row['avatar']);
            magic_file_attach("user/" . $data['username'], "avatar", $avatar_file);
        }
    }
}

if (read_bool("Clean user password & email?", true)) {
    db_query("UPDATE `ia_user` SET `password`=SHA1(CONCAT(LCASE(`username`),LCASE(`username`))), `email`='no@spam.com'");
}

//
//  Import rounds.
//
if (read_bool("Import rounds?", false)) {
    log_print("Importing rounds...");
    db_query("TRUNCATE TABLE ia_round");
    db_query("TRUNCATE TABLE ia_round_task");
    db_query("DELETE FROM ia_textblock WHERE `name` LIKE 'round/%'");
    db_query("DELETE FROM ia_textblock_revision WHERE `name` LIKE 'round/%'");
    db_query("DELETE FROM ia_file WHERE `page` LIKE 'round/%'");

    // DB query.
    $result = mysql_query("SELECT * FROM contests", $dbOldLink);
    if (!$result) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($contest = mysql_fetch_assoc($result)) {
        $contest['ID'] = strip_tags($contest['ID']);
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
if (read_bool("Import scores?", false)) {
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
if (read_bool("Import tasks?", false)) {
    db_query("TRUNCATE TABLE ia_task");
    db_query("DELETE FROM ia_file WHERE `page` LIKE 'task/%'");
    $import_text = read_bool("Import task statements?", false);
    $import_eval = read_bool("Import task evaluators?", true);
    $import_tests = read_bool("Import task tests?", true);

    if ($import_text) {
        log_print('Deleting task statements.');
        db_query("DELETE FROM ia_textblock WHERE `name` LIKE 'task/%'");
        db_query("DELETE FROM ia_textblock_revision WHERE `name` LIKE 'task/%'");
    }

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
    
        if ($import_text) {
            // Basic content.
            $textblock_content = "";
            $textblock_content .= "==Include(page=\"template/taskheader\" task_id=\"$task_id\")==\n\n";
            $textblock_content .= magic_convert_task($task_id);
            $textblock_content .= "==Include(page=\"template/taskfooter\" task_id=\"$task_id\")==";
            textblock_add_revision("task/".$task_id, $task['name'], $textblock_content, 0);
        }

        $parameters = array();

        // Handle evaluator.
        $parameters['evaluator'] = "not_imported";
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
        $parameters['okfiles'] = '1';
        if ($import_tests) {
            for ($tid = 1; $tid <= $task['evalsteps']; ++$tid) {
                // Attach input.
                magic_file_attach("task/$task_id", "grader_test$tid.in", $task_dir . "test$tid.in");

                // Attach ok file.
                if (!file_exists($task_dir . "test$tid.ok")) {
                    $parameters['okfiles'] = '0';
                    log_warn("TASK $task_id HAS NO OK FILES");
                } else {
                    magic_file_attach("task/$task_id", "grader_test$tid.ok", $task_dir . "test$tid.ok");
                }
            }
        }

        $parameters['tests'] = $task['evalsteps'];
        $parameters['timelimit'] = $task['timelimit'];
        $parameters['memlimit'] = 65536;
        $parameters['unique_output'] = '0';
        // Update parameters.
        task_update_parameters($task_id, $parameters);  
        log_print("DONE $task_id\n");
    }
}

if (read_bool('Import articles?', false)) {
    // hash article categories
    $art_cat = mysql_query("SELECT * FROM info_artcat", $dbOldLink);
    if (!$art_cat){
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    while ($cat = mysql_fetch_assoc($art_cat)) {
        $category[$cat['id']] = $cat['caption'];
    }

    // get articles    
    $articles = mysql_query("SELECT *, DATE_FORMAT(postDate, '%Y-%m-%d') AS postDate FROM info_art", $dbOldLink);
    if (!$articles) {
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }

    $article_index = "h1. Articole\n\n";
    while ($article = mysql_fetch_assoc($articles)) {
        $article['title'] = strip_tags($article['title']);
        log_print("Adding article \"".$article['title']."\" ...");
        if (!$article['visible']) {
            log_print('Ignor articolul "'.$article['title'].'"!');
            continue;
        }

        $textblock_content = 'h1. '.$article['title']."\n\n";
        $textblock_content.= "(Creat de '_".$article['userId']."_':user/".$article['userId']." la data de _".
                             $article['postDate']."_ categoria _".
                             $category[$article['catId']]."_, autor(i) _".$article['author']."_)\n\n";
        $textblock_content .= "*Continut scurt:*\n ".magic_convert_textile("NOFILE", $article['shortContent'])."\n\n";
        $textblock_content .= "*Continut lung:*\n".magic_convert_textile("NOFILE", $article['content'])."\n";

        if ($category[$article['catId']] != 'Arhiva stiri') {
            textblock_add_revision(magic_title($article['title']), $article['title'],
                                   $textblock_content, 0, $article['postDate']);
            $article_index .= "* '".htmlentities($article['title'], ENT_QUOTES)."':".magic_title($article['title'])."\n";
        }
        else {
            textblock_add_revision("news/".magic_title($article['title']),
                                   $article['title'], $textblock_content, 0,
                                   $article['postDate']);
        }
    }

    textblock_add_revision("articles", "Articole", $article_index, 0);
};

if (read_bool("Import links?", false)) {
    $resources = mysql_query("SELECT *, DATE_FORMAT(postDate, '%Y-%m-%d') AS postDate
                             FROM info_res ORDER BY postDate DESC", $dbOldLink);
    if (!$resources){
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    $arr = array();
    while ($res = mysql_fetch_assoc($resources)) {
        $arr[] = $res;
    }

    // hash resource categories
    $res_cat = mysql_query("SELECT * FROM info_rescat", $dbOldLink);
    if (!$res_cat){
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }

    $resource_index ="h1. Link-uri\n";

    $category = array();
    $total = 0;
    while ($cat = mysql_fetch_assoc($res_cat)) {
        $cnt = 0;
        foreach ($arr as $resource) {
            if ($resource['catId'] == $cat['id'] && $resource['visible']) $cnt++;
        }
        log_print("Adding resources from category \"".$cat['caption']."\"...".$cnt." found");
        $total += $cnt;
        if (!$cnt) continue;
        $resource_index .= "\nh2. ".$cat['caption']." (".$cnt." link-uri)\n\n";

        foreach ($arr as $resource) {
            if ($resource['catId'] != $cat['id'] || !$resource['visible']) continue;
            $resource_index .= "* '".htmlentities($resource['title'], ENT_QUOTES)."':".
                               $resource['href']." ( ==user(user=\"".
                               htmlentities($resource['userId'], ENT_QUOTES)."\" type=\"tiny\")==".
			       " @ ".$resource['postDate'].")\n";
            $resource_index .= htmlentities($resource['description'], ENT_QUOTES)."\n";
        }
    }
    log_print("Added ".$total." resources!\n");

    textblock_add_revision("links", "Link-uri", $resource_index, 0);
}
    
if (read_bool("Import downloads?", false)) {
    $downloads = mysql_query("SELECT *, DATE_FORMAT(postDate, '%Y-%m-%d') AS postDate
                             FROM info_down ORDER BY postDate DESC", $dbOldLink);
    if (!$downloads){
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }
    $arr = array();
    while ($down = mysql_fetch_assoc($downloads)) {
        $arr[] = $down;
    }

    // hash download categories
    $res_cat = mysql_query("SELECT * FROM info_downcat", $dbOldLink);
    if (!$res_cat){
        log_error('IMPORT: MYSQL error -> '.mysql_error($dbOldLink));
    }

    $download_index ="h1. Download-uri\n";

    $category = array();
    $total = 0;
    while ($cat = mysql_fetch_assoc($res_cat)) {
        $cnt = 0;
        foreach ($arr as $download) {
            if ($download['catId'] == $cat['id'] && $download['visible']) $cnt++;
        }
        log_print("Adding downloads from category \"".$cat['caption']."\"...".$cnt." found");
        $total += $cnt;
        if (!$cnt) continue;
        $download_index .= "\nh2. ".$cat['caption']." (".$cnt." link-uri)\n\n";

        foreach ($arr as $download) {
            if ($download['catId'] != $cat['id'] || !$download['visible']) continue;

	    $download['href'] = preg_replace('/[^a-z0-9\.\-_]/i', '_', $download['href']);
	    
            // attach the file
            $fname = $ia1_path."www/info/upload/".$download['id'];
            if (!file_exists($fname)) {
                log_warn("Attachment ".$download['href']."(".$fname.") is missing!");
                continue;
            }
            else {
	    
                magic_file_attach("downloads", $download['href'], $fname);
            }	    
            
            $download_index .= "* '".htmlentities($download['title'], ENT_QUOTES)."':downloads?".
                               $download['href']." ( _".$download['size']."_ bytes, ==user(user=\"".
                               htmlentities($download['userId'], ENT_QUOTES)."\" type=\"tiny\")==".
                               " @ ".$download['postDate']." )\n";
            $download_index .= htmlentities($download['description'], ENT_QUOTES)."\n";
        }
    }
    log_print("Added ".$total." downloads!\n");

    textblock_add_revision("downloads", "Download-uri", $download_index, 0);
}
?>
