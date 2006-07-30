<?php

// Fill news-specific context.
function news_fill_context($news_id, &$context)
{
}

// Fill task-specific context.
function task_fill_context($task_id, &$context)
{
    $context['task'] = task_get($task_id);
    $context['task_parameters'] = task_get_parameters($task_id);
}

// Fill round-specific contedt.
function round_fill_context($round_id, &$context)
{
    // FIXME: write me.
}

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

function textblock_get_owner($textblock)
{
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

// Get the context for a certain textblock.
function textblock_get_context($textblock)
{
    textblock_split_name($textblock['name'], $module, $objid);

    $context = array();
    $context['page_name'] = $textblock['name'];

    //echo "module = $module, obj = $objid";
    if ($module == 'news') {
        news_fill_context($objid, $context);
    } if ($module == 'task') {
        task_fill_context($objid, $context);
    } if ($module == 'round') {
        round_fill_context($objid, $context);
    }

    return $context;
}

function textblock_get_html($textblock)
{
    $content = $textblock['content'];
    $context = textblock_get_context($textblock);

    return wiki_process_text($content, $context);
}

// Checks if you have the permission to view a certain page.
// $permission must be one of the simple permissions.
function textblock_get_permission($textblock, $permission)
{
    $textblock_permissions = array(
            'view', 'history', 'attach-download');
    assert(false !== array_search($permission, $textblock_permissions));

    textblock_split_name($textblock['name'], $module, $objid);

    if ($module == 'news' || $module == 'task' || $module == 'round') {
        $action = $module . "-" . $permission;
        $object = textblock_get_owner($textblock);
    } else {
        $action = "wiki-" . $permission;
        $object = $textblock;
    }

    return identity_can($action, $object);
}

?>
