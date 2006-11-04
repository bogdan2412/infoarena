<?php

// WARNING: This module is rather hacky. It will probably undergo major changes.

define("TEXTBLOCK_WIKI",    1);
define("TEXTBLOCK_NEWS",    2);
define("TEXTBLOCK_TASK",    3);
define("TEXTBLOCK_ROUND",   4);
define("TEXTBLOCK_USER",    5);

// map textblock prefixes to textblock classes
$TEXTBLOCK_PREFIX = array(
    'news' => TEXTBLOCK_NEWS,
    'user' => TEXTBLOCK_USER,
    'round' => TEXTBLOCK_ROUND,
    'task' => TEXTBLOCK_TASK
);
// inverse hash
$TEXTBLOCK_PREFIX_OF = array_flip($TEXTBLOCK_PREFIX);

// Split a textblock name. Returns 2-element array: (textblock class, object id)
// Example: a textblock of name `task/adunare` is split into (TEXTBLOCK_TASK, 'adunare')
function textblock_split_name($name) {
    global $TEXTBLOCK_PREFIX;

    // Split the page url
    $path = split('/', $name);
    if (count($path) <= 0) {
        $path = array("");
    }
    $prefix = $path[0];
    array_shift($path);
    $objid = join('/', $path);

    // convert $model string into fixed constant
    $class = getattr($TEXTBLOCK_PREFIX, $prefix, TEXTBLOCK_WIKI);
    return array($class, $objid);
}

// Returns instance of model associated with given textblock or null if no such model exists.
// Note: Not all textblock classes have associated models.
// Example: a textblock named `task/adunare` is associated a `task` model instance with id `adunare`
// Example: a textblock named `homepage` is associated with textblock itself.
function textblock_get_model($textblock) {
    list($class, $obj_id) = textblock_split_name(getattr($textblock, 'name'));

    switch ($class) {
        case TEXTBLOCK_TASK:
            return task_get($obj_id);

        case TEXTBLOCK_ROUND:
            return round_get($obj_id);

        case TEXTBLOCK_USER:
            return user_get_by_username($obj_id);

        case TEXTBLOCK_NEWS:
        case TEXTBLOCK_WIKI:
            return $textblock;

        default:
            return null;
    }
}

// Checks if user can view textblock.
// This is a hacky wrapper around identity_can()
function textblock_get_permission($permission, $textblock, $user = null) {
    global $TEXTBLOCK_PREFIX_OF;

    list($class, $obj_id) = textblock_split_name(getattr($textblock, 'name'));

    if (TEXTBLOCK_WIKI == $class) {
        $action = 'wiki-'.$permission;
    }
    else {
        $action = $TEXTBLOCK_PREFIX_OF[$class].'-'.$permission;
    }

    $object = textblock_get_model($textblock);
    $result = identity_can($action, $object, $user);

    return $result;
}

?>
