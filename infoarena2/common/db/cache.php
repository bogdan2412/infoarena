<?php

// This module implements a simple SQL query cache.
// Objects are identifier by a type (user, task, etc) and an
// unique identifier. Ideally $ids should all be ints but we can
// use simple strings. All ids must be unique in their namespace.
//
// NOTE: You should never use these functions directly, instead you should
// continue to use various common/db/* functions and make those use the cache.
// If a DB function exposes the cache it's broken.
//
// NOTE: Null values can be stored (to remember that an object doesn't exist).
// NOTE: Only store complete objects in the database.
// NOTE: You should purge whenever you update.
// NOTE: Uncached SELECTs however are safe.
//
// The cache must be enabled with IA_ENABLE_DB_CACHE(default)

// Global cache variable.
// Don't touch or I'll hunt you down and eat your eyes.
$_db_cache = array();

// Store an object in the database. Override if already there.
// Always returns $object, so you don't have to use a temp variable.
function db_cache_set($type, $id, $object) {
    if (!IA_ENABLE_DB_CACHE) {
        // No-op
        return $object;
    }

    log_assert(is_array($object) || is_null($object), "Only null or array can be stored");
    global $_db_cache;
    if (!array_key_exists($type, $_db_cache)) {
        $_db_cache[$type] = array();
    }
    $_db_cache[$type][$id] = $object;
    if (IA_LOG_DB_CACHE) {
        log_print("DB CACHE STORE: $type $id");
    }
    return $object;
}

// Retrieve an object from the cache, or false if not found.
// NOTE: null is a perfectly valid result, it means that a previous query
// determined that the object is not in the database.
function db_cache_get($type, $id) {
    if (!IA_ENABLE_DB_CACHE) {
        // No-op
        return false;
    }

    global $_db_cache;
    if (!array_key_exists($type, $_db_cache)) {
        if (IA_LOG_DB_CACHE) {
            log_print("DB CACHE MISS: $type $id");
        }
        return false;
    }
    if (!array_key_exists($id, $_db_cache[$type])) {
        if (IA_LOG_DB_CACHE) {
            log_print("DB CACHE MISS: $type $id");
        }
        return false;
    }
    if (IA_LOG_DB_CACHE) {
        log_print("DB CACHE HIT: $type $id");
    }
    return $_db_cache[$type][$id];
}

// Purge the cache.
// If $id is null then all objects of a specified $type are purged.
// If $type is null then the entire cache is purged.
function db_cache_purge($type = null, $id = null) {
    global $_db_cache;
    if (!IA_ENABLE_DB_CACHE) {
        // No-op
        return;
    }

    if (IA_LOG_DB_CACHE) {
        log_print("DB CACHE PURGE: $type $id");
    }
    if (is_null($type) && !is_null($id)) {
        log_error("If $type is null then $id also has to be null");
    }
    if (is_null($type)) {
        // Purge the entire cache.
        unset($GLOBALS['_db_cache']);
    } else if (is_null($id)) {
        // Purge an entire namespace.
        if (array_key_exists($type, $_db_cache)) {
            unset($_db_cache[$type]);
        }
    } else {
        // Purge a single object.
        if (array_key_exists($type, $_db_cache) &&
            array_key_exists($id, $_db_cache[$type])) {
            unset($_db_cache[$type][$id]);
        }
    }
}

?>
