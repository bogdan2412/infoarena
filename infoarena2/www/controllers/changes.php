<?php

require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "www/format/format.php");
require_once(IA_ROOT . "common/db/textblock.php");

// View recent changes.
function controller_changes($page_name) {
    $pager_opts = pager_init_options();
    $view = array();
    $title = 'Schimbari pe www.infoarena.ro';
    $prefix = request('prefix', '');
    $revisions = textblock_get_changes($prefix, false, true, $pager_opts['display_entries']);

    // FIXME: horrible horrible hack to add revision ids.
    $rev_ids = array();
    for ($i = 0; $i < count($revisions); ++$i) {
        $name = strtolower($revisions[$i]['name']);
        if (array_key_exists($name, $rev_ids)) {
            --$rev_ids[$name];
        } else {
            $rev_ids[$name] = textblock_get_revision_count($name);
        }
        $revisions[$i]['revision_id'] = $rev_ids[$name];
    }

    if (request('format') == 'rss') {
        $view = array();
        $view['channel']['title'] = 'Modificari pe infoarena';
        $view['channel']['link'] = url_changes();
        $view['channel']['description'] = 'Ultimele modificari din wiki-ul http://infoarena.ro';
        $view['channel']['language'] = 'ro-ro'; 
        $view['channel']['copyright'] = '&copy; 2006 - asociatia infoarena';

        $view['item'] = array();

        foreach ($revisions as $rev) {
            log_print_r($rev);
            $item = array();
            $item['title'] = sprintf("%s modificat de %s",
                    $rev['title'] , $rev['user_name']);

            $userlink = format_user_tiny($rev['user_name'], $rev['user_fullname']);
            $pagelink = format_link(url_textblock($rev['name'], true), $rev['title']);
            $diffurl = url_textblock_diff($rev['name'], $rev['revision_id'], $rev['revision_id'] - 1);
            $difflink = format_link($diffurl, "modificari");
            $tstamp = format_date($rev['timestamp']);
            $item['description'] = "La data de $tstamp pagina $pagelink a fost modificata de $userlink($difflink).";

            $item['guid'] = sha1($rev['name'] . $rev['timestamp']);
            $item['link'] = url_absolute(url_textblock($rev['name']));

            $view['item'][] = $item;
        }
        execute_view_die('views/rss.php', $view);
    } else {
        $view = array();
        $view['title'] = 'Modificari pe infoarena';
        $view['page_name'] = 'changes';
        $view['revisions'] = $revisions;
        execute_view_die('views/changes.php', $view);
    }
}

?>
