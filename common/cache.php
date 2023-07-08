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
            @unlink($file_name);
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
    require_once(Config::ROOT . 'www/utilities.php');
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
    foreach (glob(Config::ROOT . "cache/*", GLOB_ONLYDIR) as $dir_name) {
        _recursive_delete($dir_name);
    }

    foreach (glob(Config::ROOT . "cache/*") as $file_name) {
        unlink($file_name);
    }
}

//
// Memory caching implementation depends on IA_MEM_CACHE_METHOD.
// There are only four functions, which should be enough for just about
// everything.
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
//      mem_cache_purge deletes everything from the cache.
//      Try to avoid calling it.
//
if (IA_MEM_CACHE_METHOD == 'none') {
    // Fake cache implementation
    function mem_cache_get($cache_id) {
        return false;
    }

    function mem_cache_set($cache_id, $object, $ttl = IA_MEM_CACHE_EXPIRATION) {
        return $object;
    }

    function mem_cache_delete($cache_id) {
        return false;
    }

    function mem_cache_purge() {
        return false;
    }
} else if (IA_MEM_CACHE_METHOD == 'eaccelerator') {
    // eAccelerator SHM memory cache
    function mem_cache_get($cache_id) {
        $res = @eaccelerator_get($cache_id);
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
        log_assert($object !== false, "Can't cache false values");
        if (!@eaccelerator_put($cache_id, serialize($object), $ttl)) {
            log_warn('Error occurred while storing ' . $cache_id . ' in cache');
        }
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: store $cache_id");
        }
        return $object;
    }

    function mem_cache_delete($cache_id) {
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: delete $cache_id");
        }
        return @eaccelerator_rm($cache_id);
    }

    function mem_cache_purge() {
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: purge");
        }
        return @eaccelerator_gc();
    }
} else if (IA_MEM_CACHE_METHOD == 'memcached') {
    // Memcached cache implementation.
    $_memcache = memcache_pconnect(IA_MEMCACHED_HOST, IA_MEMCACHED_PORT);

    function mem_cache_get($cache_id) {
        global $_memcache;
        $res = @memcache_get($_memcache, $cache_id);
        if ($res === false || $res === null) {
            if (IA_LOG_MEM_CACHE) {
                log_print("MEM CACHE: miss on $cache_id");
            }
            return false;
        } else {
            if (IA_LOG_MEM_CACHE) {
                log_print("MEM CACHE: hit on $cache_id");
            }
            return $res;
        }
    }

    function mem_cache_set($cache_id, $object, $ttl = IA_MEM_CACHE_EXPIRATION) {
        global $_memcache;
        log_assert($object !== false, "Can't cache false values");
        if (!@memcache_set($_memcache, $cache_id, $object, 0, $ttl)) {
            log_warn('Error occurred while storing ' . $cache_id . ' in cache');
        }
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: store $cache_id");
        }
        return $object;
    }

    function mem_cache_delete($cache_id) {
        global $_memcache;
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: delete $cache_id");
        }
        return @memcache_delete($_memcache, $cache_id);
    }

    function mem_cache_purge() {
        global $_memcache;
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: purge");
        }
        return @memcache_flush($_memcache);
    }

    function mem_cache_multiget($cache_ids) {
        global $_memcache;
        log_assert(is_array($cache_ids));
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: multiget " . implode(' ', $cache_ids));
        }

        $res = @memcache_get($_memcache, $cache_ids);
        if ($res === null)
            $res = array();

        if (count($cache_ids) > 0) {
            log_print('MEM CACHE: multiget hit ' . count($res) . '/' .
                       count($cache_ids));
        }

        return $res;
    }
} else if (IA_MEM_CACHE_METHOD == 'apc') {
    // APC cache implementation
    function mem_cache_get($cache_id) {
        $res = @apc_fetch($cache_id);
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
        log_assert($object !== false, "Can't cache false values");
        if (!@apc_store($cache_id, serialize($object), $ttl)) {
            log_warn('Error occurred while storing ' . $cache_id . ' in cache');
        }
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: store $cache_id");
        }
        return $object;
    }

    function mem_cache_delete($cache_id) {
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: delete $cache_id");
        }
        return @apc_delete($cache_id);
    }

    function mem_cache_purge() {
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: purge");
        }
        return @apc_clear_cache();
    }
}

if (IA_MEM_CACHE_METHOD != 'memcached') {
    function mem_cache_multiget($cache_ids) {
        log_assert(is_array($cache_ids));
        if (IA_LOG_MEM_CACHE) {
            log_print("MEM CACHE: multiget " . implode(' ', $cache_ids));
        }

        $res = array();
        foreach ($cache_ids as $cache_key) {
            $result = mem_cache_get($cache_key);
            if ($result !== false) {
                $res[$cache_key] = $result;
            }
        }

        if (count($cache_ids) > 0) {
            log_print('MEM CACHE: multiget hit ' . count($res) . '/' .
                       count($cache_ids));
        }

        return $res;
    }
}
