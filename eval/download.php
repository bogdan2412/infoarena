<?php

require_once(IA_ROOT_DIR . 'common/common.php');
require_once(IA_ROOT_DIR . 'common/db/attachment.php');

function copy_grader_file($task, $filename, $target) {
    $attempts = 0;
    while (true) {
        $result = copy_attachment_file($task['page_name'], "grader_".$filename, $target);
        if ($result) {
            return true;
        }
        if ($result == false && $attempts < IA_JUDGE_MAX_GRADER_DOWNLOAD_RETRIES) {
            ++$attempts;
            log_print("Failed downloading grader file... sleep and retry");
            milisleep(1000);
            continue;
        }
        return false;
    }
}

// Copy a grader file over to some other location.
// This will download the file from the server and cache it.
//
// Return success value.
function copy_attachment_file($pagename, $filename, $target) {
    $pagename = normalize_page_name($pagename);
    eval_assert(is_page_name($pagename),
                "Invalid page name '$pagename'");
    eval_assert(is_attachment_name($filename),
                "Invalid attachment name '$filename'");

    // Get attachment from database.
    $att = attachment_get($filename, $pagename);
    if (!$att) {
        log_warn("Attachment $pagename/$filename not found.");
        return false;
    }

    // Make grader dir, in case it doesn't exit.
    @mkdir(IA_ROOT_DIR.'eval/grader_cache/'.$pagename.'/', 0700, true);

    // My cached version timestamp
    $cachefname = IA_ROOT_DIR.'eval/grader_cache/'.$pagename.'/'.$filename;

    clearstatcache();
    // Check modification time and file size.
    $cachemtime = @filemtime($cachefname);
    $servermtime = db_date_parse($att['timestamp']);
    $cachefsize = @filesize($cachefname);
    $serverfsize = $att['size'];

    log_print("Server file mtime $servermtime size $serverfsize");
    log_print("Cache file mtime $cachemtime size $cachefsize");
    if ($cachemtime === false || $cachemtime < $servermtime ||
        $cachefsize === false || $cachefsize != $att['size']) {
        $curl = curl_init();
        // Can't use url_attachment here because it's in www.
        curl_setopt($curl, CURLOPT_URL, IA_URL . "$pagename?action=download&file=$filename");
        curl_setopt($curl, CURLOPT_USERPWD, IA_JUDGE_USERNAME . ":" . IA_JUDGE_PASSWORD);
        curl_setopt($curl, CURLOPT_INTERFACE, "193.105.239.206"); 
        $cachefd = fopen($cachefname, "wb");
        if (!$cachefd) {
            log_warn("Failed to open $pagename/$filename for writing.");
            return false;
        }

        curl_setopt($curl, CURLOPT_FILE, $cachefd);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        log_print("Downloading new version of $pagename/$filename...");
        if (!curl_exec($curl)) {
            log_warn("Failed curl download for $pagename/$filename.");
            log_warn("Curl says: ".curl_error($curl));
            return false;
        }
        curl_close($curl);
        if (!fclose($cachefd)) {
            log_warn("Failed closing $pagename/$filename.");
            return false;
        }

        clearstatcache();
        $newcachemtime = @filemtime($cachefname);
        $newcachefsize = @filesize($cachefname);
        log_print("Downloaded new file $pagename/$filename, mtime $newcachemtime size $newcachefsize");
        if ($newcachefsize != $serverfsize) {
            log_warn("Downloaded file has different size: $newcachefsize != $serverfsize");
            return false;
        }
    } else {
        log_print("Using cached $pagename/$filename");
    }
    if (!copy($cachefname, $target)) {
        log_warn("Failed copying grader file $pagename/$filename");
        return false;
    }
    return true;
}
