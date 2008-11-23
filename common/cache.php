<?php
require_once(IA_ROOT_DIR . "common/string.php");

// Check if there is something in the cache newer that date.
// If date is null age doesn't matter.
// Date must be unix timestamp.
// Old stuff is deleted.
//
// Only returns true/false.

// Used internally to determine file path from cache_id
function _disk_cache_path($cache_id) {
    return IA_ROOT_DIR . "cache/" . $cache_id;
}

// Recursively delete directories
function _recursive_delete($path) {
    $path = realpath($path);
    log_assert($path != IA_ROOT_DIR . "cache/" &&
               starts_with($path, IA_ROOT_DIR . "cache/"));
    foreach (glob($path . "/*") as $file_name) {
        if (is_dir($file_name)) {
            _recursive_delete($file_name);
        } else if (is_file($file_name)) {
            unlink($file_name);
        }
    }
    rmdir($path);
}

function disk_cache_has($cache_id, $date = null) {
    $file_name = _disk_cache_path($cache_id);

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
    $file_name = _disk_cache_path($cache_id);

    if (@is_readable($file_name)) {
        if (IA_LOG_DISK_CACHE) {
            log_print("CACHE: DISK: HIT on $cache_id");
        }
        return file_get_contents($file_name);
    } else {
        if (IA_LOG_DISK_CACHE) {
            log_print("CACHE: DISK: MISS on $cache_id");
        }
        return false;
    }
}

// If $cache_id is in cache, then pass it to the client.
// Fails if not found.
function disk_cache_serve($cache_id, $http_file_name, $mime_type = null) {
    require_once(IA_ROOT_DIR . 'www/utilities.php');
    $file_name = _disk_cache_path($cache_id);

    if (IA_LOG_DISK_CACHE) {
        log_print("CACHE: DISK: SERVE $cache_id");
    }
    http_serve($file_name, $http_file_name, $mime_type);
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

    if (IA_LOG_DISK_CACHE) {
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
    $file_name = _disk_cache_path($cache_id);
    @unlink($file_name);
}

// Delete the entire disk cache
function disk_cache_purge() {
    foreach (glob(IA_ROOT_DIR . "cache/*", GLOB_ONLYDIR) as $dir_name) {
        _recursive_delete($dir_name);
    }

    foreach (glob(IA_ROOT_DIR . "cache/*") as $file_name) {
        unlink($file_name);
    }
}

//
// Memory caching implementation depends on IA_MEM_CACHE_METHOD.
// There are only four functions, which should be enough for just about everything.
//
// You can store arrays, ints, string, null but not booleans because they're
// used by the mem_cache_get function. You could store booleans as 0/1 however.
//
// FUNCTIONS:
//      mem_cache_get gets an object from the cache, or FALSE if not found.
//      A null result means it a null value was stored.
//
//      mem_cache_set places an object in the cache with a certain ttl and
//      returns the untouched object. Booleans can't be stored.
//
//      mem_cache_delete removes something from the cache, use this when the
//      object is no longer valid.
//
//      mem_cache_purge deletes everything from the cache. Try to avoid calling it.
//
// FIXME: cache is not logged. See IA_LOG_MEM_CACHE.
if (IA_MEM_CACHE_METHOD == 'none') {

    // Fake cache implementation/

    function mem_cache_get($cache_id) {
        return false;
    }

    function mem_cache_set($cache_id, $object, $ttl = IA_MEM_CACHE_EXPIRATION) {
        return $object;
    }

    function mem_cache_delete($cache_id) {
    }

    function mem_cache_purge() {
    }

} else if (IA_MEM_CACHE_METHOD == 'eaccelerator') {

    // eAccelerator SHM memory cache

    function mem_cache_get($cache_id) {
        $res = eaccelerator_get($cache_id);
        if ($res === null) {
            if (IA_LOG_MEM_CACHE) {
                log_print("MEM CACHE: miss on $cache_id");
            }
            return false;
        } else {
            if (IA_LOG_MEM_CACHE) {
                log_print("MEM CACHE: hit on $cache_id");
            }
            return unserialize($res);
        }
    }

    function mem_cache_set($cache_id, $object, $ttl = IA_MEM_CACHE_EXPIRATION) {
        log_assert($object !== 'false', "Can't cache false values");
        eaccelerator_put($cache_id, serialize($object), $ttl);

        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: store $cache_id");
        }

        return $object;
    }

    function mem_cache_delete($cache_id) {
        eaccelerator_rm($cache_id);
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: delete $cache_id");
        }
    }

    // Purge the entire SHM cache.
    // FIXME: This does not work
    function mem_cache_purge() {
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: purge");
        }
        eaccelerator_gc();
    }

} else if (IA_MEM_CACHE_METHOD == 'memcached') {

    // memcached cache implementation.

    $_memcache = memcache_pconnect('localhost');

    function mem_cache_get($cache_id) {
        global $_memcache;
        $res = memcache_get($_memcache, $cache_id);
        if ($res === false) {
            if (IA_LOG_MEM_CACHE) {
                log_print("MEM CACHE: miss on $cache_id");
            }
            return false;
        } else {
            if (IA_LOG_MEM_CACHE) {
                log_print("MEM CACHE: hit on $cache_id");
            }
            return unserialize($res);
        }
    }

    function mem_cache_set($cache_id, $object, $ttl = IA_MEM_CACHE_EXPIRATION) {
        global $_memcache;
        log_assert($object !== 'false', "Can't cache false values");
        memcache_set($_memcache, $cache_id, serialize($object), 0, $ttl);

        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: store $cache_id");
        }
        return $object;
    }

    function mem_cache_delete($cache_id) {
        global $_memcache;
        memcache_delete($_memcache, $cache_id);

        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: delete $cache_id");
        }
    }

    function mem_cache_purge() {
        global $_memcache;
        memcache_flush($_memcache);

        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: purge");
        }
    }
}

?>
