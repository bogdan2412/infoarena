<?php

require_once(IA_ROOT_DIR . 'common/common.php');
require_once(IA_ROOT_DIR . 'common/db/attachment.php');

function copy_grader_file($task, $filename, $target)
{
    return copy_attachment_file($task['page_name'], "grader_".$filename, $target);
}

// Copy a grader file over to some other location.
// This will download the file from the server and cache it.
//
// FIXME: Don't cache if www runs locally.
function copy_attachment_file($pagename, $filename, $target)
{
    $pagename = normalize_page_name($pagename);
    log_assert(is_page_name($pagename), "Invalid page name '$pagename'");
    log_assert(is_attachment_name($filename), "Invalid attachment name '$filename'");

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

    if ($cachemtime === false || $cachemtime < $servermtime ||
        $cachefsize === false || $cachefsize != $att['size']) {
        $curl = curl_init();
        // Can't use url_attachment here because it's in www.
        curl_setopt($curl, CURLOPT_URL, IA_URL . "$pagename?action=download&file=$filename");
        curl_setopt($curl, CURLOPT_USERPWD, IA_JUDGE_USERNAME . ":" . IA_JUDGE_PASSWORD);

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

        log_print("Downloaded new version of $pagename/$filename.");
    } else {
        log_print("Using cached $pagename/$filename");
    }
    if (!copy($cachefname, $target)) {
        log_warn("Failed copying grader file $pagename/$filename");
        return false;
    }
    return true;
}

?>
