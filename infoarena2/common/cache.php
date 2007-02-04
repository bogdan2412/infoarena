<?php

// Check if there is something in the cache newer that date.
// If date is null age doesn't matter.
// Date must be unix timestamp.
// Old stuff is deleted.
//
// Only returns true/false.
function disk_cache_has($cache_id, $date = null) {
    $file_name = IA_ROOT_DIR . 'cache/' . $cache_id;

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
function disk_cache_get($cache_id) {
    $file_name = IA_ROOT_DIR . 'cache/' . $cache_id;

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
function disk_cache_serve($cache_id, $http_file_name, $mime_type = null) {
    require_once(IA_ROOT_DIR . 'www/utilities.php');
    $file_name = IA_ROOT_DIR . 'cache/' . $cache_id;

    if (IA_LOG_CACHE) {
        log_print("CACHE: DISK: SERVE $cache_id");
    }
    http_serve($file_name, $http_file_name, $mime_type);
}

// Place an object in the cache.
// Object should expire after $ttl, but it's only a hint.
// Always returns $buffer.
function disk_cache_set($cache_id, $buffer, $ttl = 0) {
    $file_name = IA_ROOT_DIR . 'cache/' . $cache_id;

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

// Delete something from the disk cache.
function disk_cache_delete($cache_id) {
    $file_name = IA_ROOT_DIR . 'cache/' . $cache_id;
    @unlink($file_name);
}

// Delete the entire disk cache
function disk_cache_purge() {
    foreach (glob(IA_ROOT_DIR . 'cache/[^.]*') as $file_name) {
        @unlink($file_name);
    }
}

if (IA_MEM_CACHE_METHOD == 'none') {

    // Fake cache

    function mem_cache_get($cache_id) {
        return false;
    }

    function mem_cache_set($cache_id, $buffer, $ttl = 10) {
    }

    function mem_cache_delete($cache_id) {
    }

    // Purge the entire SHM cache.
    // FIXME: does this actually work?
    function mem_cache_purge() {
    }

} else if (IA_MEM_CACHE_METHOD == 'eaccelerator') {

    // eAccelerator SHM memory cache

    function mem_cache_get($cache_id) {
        $res = eaccelerator_get($cache_id);
        if ($res === null) {
            return false;
        } else {
            return unserialize($res);
        }
    }

    function mem_cache_set($cache_id, $buffer, $ttl = 10) {
        log_assert($object !== 'false', "Can't cache false values");
        eaccelerator_put($cache_id, $buffer, $ttl);
    }

    function mem_cache_delete($cache_id) {
        eaccelerator_rm($cache_id);
    }

    // Purge the entire SHM cache.
    // FIXME: does this actually work?
    function mem_cache_purge() {
        eaccelerator_gc();
    }

} else if (IA_MEM_CACHE_METHOD == 'memcached') {

    $_memcache = memcache_pconnect('localhost');

    function mem_cache_get($cache_id) {
        global $_memcache;
        $res = memcache_get($_memcache, $cache_id);
        if ($res === false) {
            return false;
        } else {
            return unserialize($res);
        }
    }

    function mem_cache_set($cache_id, $object, $ttl = 10) {
        global $_memcache;
        log_assert($object !== 'false', "Can't cache false values");
        memcache_set($_memcache, $cache_id, serialize($object), 0, $ttl);
    }

    function mem_cache_delete($cache_id) {
        global $_memcache;
        memcache_delete($_memcache, $cache_id);
    }

    // Purge the entire SHM cache.
    // FIXME: does this actually work?
    function mem_cache_purge() {
        global $_memcache;
        memcache_flush($_memcache);
    }
}

?>
