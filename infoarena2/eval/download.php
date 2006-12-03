<?php

require_once(IA_ROOT . 'common/common.php');
require_once(IA_ROOT . 'common/db/attachment.php');

function copy_grader_file($task, $filename, $target)
{
    return copy_attachment_file($pagename, "grader_".$filename, $target);
}

// Copy a grader file over to some other location.
// This will download the file from the server and cache it.
//
// FIXME: Don't download if www runs locally.
function copy_attachment_file($pagename, $filename, $target)
{
    log_assert(is_page_name($pagename));
    log_assert(is_attachment_name($pagename));

    // Get attachment from database.
    $att = attachment_get($filename, $pagename);
    if (!$att) {
        log_warn("Attachment $pagename/$filename not found.");
        return false;
    }

    // Make grader dir, in case it doesn't exit.
    @mkdir(IA_GRADER_CACHE_DIR . $pagename . '/', 0700, true);
    // My cached version timestamp
    $cachefname = IA_GRADER_CACHE_DIR . $pagename . '/' . $filename;

    clearstatcache();
    $cachemtime = @filemtime($cachefname);
    $servermtime = strtotime($att['timestamp']);

    //$date_format = "Y-m-d H:i:s";
    //log_print("cached stamp $cachemtime(" . date($date_format, $cachemtime) . ") ".
    //          "server stamp $servermtime(" . date($date_format, $servermtime) . ")");

    if ($cachemtime === null || $cachemtime < $servermtime) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, url_attachment($pagename, $filename, true));
        curl_setopt($curl, CURLOPT_USERPWD, IA_JUDGE_USERNAME . ":" . IA_JUDGE_PASSWD);

        $cachefd = fopen($cachefname, "wb");
        if (!$cachefd) {
            log_warn("Failed to open $pagename/$filename for writing.");
            return false;
        }

        curl_setopt($curl, CURLOPT_FILE, $cachefd);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        if (!curl_exec($curl)) {
            log_warn("Failed curl download for $pagename/$filename.");
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
