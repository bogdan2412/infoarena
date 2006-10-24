<?php

// Split a textblock name into a module and an object.
function textblock_split_name($name, &$module, &$objid)
{
    // Split the page url.
    $path = split('/', $name);
    if (count($path) <= 0) {
        $path = array("");
    }

    $module = $path[0];
    array_shift($path);
    $objid = join('/', $path);
}

function textblock_get_owner($textblock) {
    textblock_split_name($textblock['name'], $module, $objid);

    if ($module == 'news') {
        //return get_news($objid);
        return null;
    } if ($module == 'task') {
        return task_get($objid);
    } if ($module == 'round') {
        return round_get($objid);
    }
    return null;
}

function textblock_get_html($textblock) {
    $content = $textblock['text'];
    return wiki_process_text($content);
}

// Checks if you have the permission to view a certain page.
// $permission must be one of the simple permissions.
function textblock_get_permission($textblock, $permission)
{
    $textblock_permissions = array('view', 'history', 'attach-download');
    log_assert(array_search($permission, $textblock_permissions) !== false,
              'Invalid textblock permission: ' . $permission);

    textblock_split_name($textblock['name'], $module, $objid);

    if ($module == 'news' || $module == 'task' || $module == 'round') {
        $action = $module . "-" . $permission;
        $object = textblock_get_owner($textblock);
    }
    else {
        $action = "wiki-" . $permission;
        $object = $textblock;
    }

    return identity_can($action, $object);
}

?>
