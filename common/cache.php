<?php
require_once(Config::ROOT . "common/string.php");

// Check if there is something in the cache newer that date.
// If date is null age doesn't matter.
// Date must be unix timestamp.
// Old stuff is deleted.
//
// Only returns true/false.

// Used internally to determine file path from cache_id
function _disk_cache_path($cache_id) {
    return Config::ROOT . "cache/" . $cache_id;
}

// Recursively delete directories
function _recursive_delete($path) {
    $path = realpath($path);
    log_assert($path != Config::ROOT . "cache/" &&
               starts_with($path, Config::ROOT . "cache/"));
    foreach (glob($path . "/*") as $file_name) {
        if (is_dir($file_name)) {
            _recursive_delete($file_name);
        } else if (is_file($file_name)) {
            unlink($file_name);
        }
    }
    rmdir($path);
}

// Get an object from the cache, or FALSE if nothing is found.
function disk_cache_get($cache_id) {
    $file_name = _disk_cache_path($cache_id);

    if (@is_readable($file_name)) {
        if (Config::LOG_DISK_CACHE) {
            log_print("CACHE: DISK: HIT on $cache_id");
        }
        return file_get_contents($file_name);
    } else {
        if (Config::LOG_DISK_CACHE) {
            log_print("CACHE: DISK: MISS on $cache_id");
        }
        return false;
    }
}

// Place an object in the cache.
// Object should expire after $ttl, but it's only a hint.
// Always returns $buffer.
function disk_cache_set($cache_id, $buffer, $ttl = 0) {
    $file_name = _disk_cache_path($cache_id);

    if (!is_dir(dirname($file_name))) {
        log_assert(!file_exists(dirname($file_name)),
                   "Cache directory contains invalid files");

        $old_umask = umask(0);
        @mkdir(dirname($file_name), 0777, true);
        umask($old_umask);
    }
    $ret = @file_put_contents($file_name, $buffer, LOCK_EX);

    if (Config::LOG_DISK_CACHE) {
        if ($ret) {
            log_print("CACHE: DISK: SET $cache_id");
        } else {
            log_warn("CACHE: DISK: FAIL SET $cache_id");
        }
    }

    return $buffer;
}

// Delete the entire disk cache
function disk_cache_purge() {
    foreach (glob(Config::ROOT . "cache/*", GLOB_ONLYDIR) as $dir_name) {
        _recursive_delete($dir_name);
    }

    foreach (glob(Config::ROOT . "cache/*") as $file_name) {
        unlink($file_name);
    }
}
