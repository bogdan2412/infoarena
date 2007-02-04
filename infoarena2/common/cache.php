<?php

// Check if there is something in the cache newer that date.
// If date is null age doesn't matter.
// Date must be unix timestamp.
// Old stuff is deleted.
//
// Only returns true/false.
function cache_has($cache_id, $date = null) {
    $file_name = IA_CACHE_DIR . $cache_id;

    if (!@is_readable($file_name)) {
        return false;
    } else {
        if (is_null($date) || $date === false) {
            return true;
        }

        // Check mtime
        $mtime = @filemtime($file_name);

        // Delete old stuff.
        if ($mtime === false || $mtime < $date) {
            @unlink($fname);
            return false;
        } else {
            return true;
        }
    }
}

// Get an object from the cache, or FALSE if nothing is found.
function cache_get($cache_id) {
    $file_name = IA_CACHE_DIR . $cache_id;

    if (@is_readable($file_name)) {
        if (IA_LOG_CACHE) {
            log_print("CACHE: DISK: HIT on $cache_id");
        }
        return file_get_contents($file_name);
    } else {
        if (IA_LOG_CACHE) {
            log_print("CACHE: DISK: MISS on $cache_id");
        }
        return false;
    }
}

// If $cache_id is in cache, then pass it to the client.
// Fails if not found.
function cache_serve($cache_id, $http_file_name, $mime_type = null) {
    require_once(IA_ROOT_DIR . 'www/utilities.php');
    $file_name = IA_CACHE_DIR . $cache_id;

    if (IA_LOG_CACHE) {
        log_print("CACHE: DISK: SERVE $cache_id");
    }
    http_serve($file_name, $http_file_name, $mime_type);
}

// Place an object in the cache.
// Object should expire after $ttl, but it's only a hint.
// Always returns $buffer.
function cache_set($cache_id, $buffer, $ttl = 0) {
    $file_name = IA_CACHE_DIR . $cache_id;

    $ret = @file_put_contents($file_name, $buffer, LOCK_EX);

    if (IA_LOG_CACHE) {
        if ($ret) {
            log_print("CACHE: DISK: SET $cache_id");
        } else {
            log_warn("CACHE: DISK: FAIL SET $cache_id");
        }
    }

    return $buffer;
}

?>
