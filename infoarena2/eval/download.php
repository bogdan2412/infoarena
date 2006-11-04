<?php

// Requires a certain grader file. The file is downloaded from the
// "eval_$filename" attachment of the "task/$taskname" wiki page.
// Return true on succes and false on failure.
function require_grader_file($taskname, $filename)
{
    //log_print("Requested $taskname/$filename.");

    // Get attachment from database.
    $att = attachment_get("grader_$filename", "task/$taskname");
    if (!$att) {
        log_print("Attachment task/$taskname?grader_$filename not found.");
        return false;
    }

    // Make grader dir, in case it doesn't exit.
    @mkdir(IA_GRADER_DIR . $taskname . '/', 0700, true);
    // My cached version timestamp
    $cachefname = IA_GRADER_DIR . $taskname . '/' . $filename;

    clearstatcache();
    $cachemtime = filemtime($cachefname);
    $servermtime = strtotime($att['timestamp']);

    //$date_format = "Y-m-d H:i:s";
    //log_print("cached stamp $cachemtime(" . date($date_format, $cachemtime) . ") ".
    //          "server stamp $servermtime(" . date($date_format, $servermtime) . ")");

    if ($cachemtime === null || $cachemtime < $servermtime) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, IA_URL . "task/$taskname?action=download&file=grader_$filename");
        curl_setopt($curl, CURLOPT_USERPWD, IA_JUDGE_USERNAME . ":" . IA_JUDGE_PASSWD);

        $cachefd = fopen($cachefname, "wb");
        if (!$cachefd) {
            log_print("Failed to open $taskname/$filename for writing.");
        }

        curl_setopt($curl, CURLOPT_FILE, $cachefd);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        if (!curl_exec($curl)) {
            log_print("Failed curl download for $taskname/$filename.");
            return false;
        }
        curl_close($curl);
        if (!fclose($cachefd)) {
            log_print("Failed closing $taskname/$filename.");
        }

        log_print("Downloaded new version of $taskname/$filename.");
        return true;
    } else {
        log_print("Using cached $taskname/$filename");
        return true;
    }
}

?>
