<?php

// Copy a grader file over to some other location.
// This will download the file from the server and cache it.
//
// FIXME: Don't download if www runs locally.
function copy_grader_file($taskname, $filename, $target)
{
    // Get attachment from database.
    $att = attachment_get("grader_$filename", TB_TASK_PREFIX."$taskname");
    if (!$att) {
        log_warn("Attachment ".TB_TASK_PREFIX."$taskname?grader_$filename not found.");
        return false;
    }

    // Make grader dir, in case it doesn't exit.
    @mkdir(IA_GRADER_CACHE_DIR . $taskname . '/', 0700, true);
    // My cached version timestamp
    $cachefname = IA_GRADER_CACHE_DIR . $taskname . '/' . $filename;

    clearstatcache();
    $cachemtime = @filemtime($cachefname);
    $servermtime = strtotime($att['timestamp']);

    //$date_format = "Y-m-d H:i:s";
    //log_print("cached stamp $cachemtime(" . date($date_format, $cachemtime) . ") ".
    //          "server stamp $servermtime(" . date($date_format, $servermtime) . ")");

    if ($cachemtime === null || $cachemtime < $servermtime) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, IA_URL . TB_TASK_PREFIX."$taskname?action=download&file=grader_$filename");
        curl_setopt($curl, CURLOPT_USERPWD, IA_JUDGE_USERNAME . ":" . IA_JUDGE_PASSWD);

        $cachefd = fopen($cachefname, "wb");
        if (!$cachefd) {
            log_warn("Failed to open $taskname/$filename for writing.");
            return false;
        }

        curl_setopt($curl, CURLOPT_FILE, $cachefd);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        if (!curl_exec($curl)) {
            log_warn("Failed curl download for $taskname/$filename.");
            return false;
        }
        curl_close($curl);
        if (!fclose($cachefd)) {
            log_warn("Failed closing $taskname/$filename.");
            return false;
        }

        log_print("Downloaded new version of $taskname/$filename.");
    } else {
        log_print("Using cached $taskname/$filename");
    }
    if (!copy($cachefname, $target)) {
        log_warn("Failed copying grader file $taskname/$filename");
        return false;
    }
    return true;
}

?>
