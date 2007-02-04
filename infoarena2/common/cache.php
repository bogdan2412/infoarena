<?php

// Check if there's an up-to-date cache entity
function cache_query($cache_id, $date = null) {
    $fname = IA_CACHE_DIR . $cache_id;
    if (!file_exists($fname)) {
        return null;
    } else {
        $mtime = @filemtime($fname);
        // Ignore old stuff.
        if ($date !== null && ($mtime === false || $mtime < $date)) {
            @unlink($fname);
            return null;
        } else {
            return $fname;
        }
    }
}

// Loads blob from cache, or returns null if not found.
// if $date(unix timestamp) is not null it will fail if the file is older.
//
// FIXME: this code is retarded.
// FIxME: properly check stuff.
function cache_load($cache_id, $date = null) {
    $fname = cache_query($cache_id, $date);
    if (!is_null($fname)) {
        $res = file_get_contents($fname);
        if ($res === false) {
            $res = null;
        }
    } else {
        $res = null;
    }

    // Yay, return
    if ($res === null) {
        if (IA_LOG_CACHE) {
            log_print("CACHE: miss on $cache_id(from $fname)");
        }
        return null;
    } else {
        if (IA_LOG_CACHE) {
            log_print("CACHE: hit on $cache_id");
        }
        return $res;
    }
}

// Sweep the cache
function cache_sweep() {
    log_warn("CACHE: Sweeping not implemented.");
}

// Add something to the cache.
function cache_save($cache_id, $buffer) {
    /*if (cache_usage() > IA_CACHE_SIZE) {
        // cache is full
        log_warn('Cache is full.');
        cache_sweep();
        return false;
    }*/

    $filename = IA_CACHE_DIR . $cache_id;
    $ret = @file_put_contents($filename, $buffer, LOCK_EX);
    // A broken cacke is fairly harmless, especially in debug.
    // Throwing up here results in no visible images, which tends to suck.
    if (false === $ret) {
        log_warn('CACHE: Could not create file ' . $filename);
        return false;
    }

    if (IA_LOG_CACHE) {
        log_print("CACHE: Saved $cache_id");
    }

    return true;
}

// Calculate cache usage.
function cache_usage() {
    // scan all files in image cache directory
    $nodes = scandir(IA_CACHE_DIR);
    $files = array();
    foreach ($nodes as $node) {
        if (!is_dir($node)) {
            $files[] = $node;
        }
    }

    // sum up file size
    $total = 0;
    foreach ($files as $file) {
        $fsize = filesize(IA_CACHE_DIR . $file);
        if (false === $fsize) {
            log_warn('CACHE: Could not determine file size of ' . IA_CACHE_DIR . $file);
        }
        $total += $fsize;
    }

    return $total;
}

?>
