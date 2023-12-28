<?php

require_once(Config::ROOT . "www/format/pager.php");
require_once(Config::ROOT . "www/format/format.php");
require_once(Config::ROOT . "common/db/textblock.php");

// View recent changes.
function controller_changes($page_name) {
  Identity::enforceViewChanges();

  $pager_opts = pager_init_options();
  $view = array();
  $prefix = request('prefix', '');
  $revisions = textblock_get_changes($prefix, false, true,
                                     $pager_opts["first_entry"],
                                     $pager_opts['display_entries']);

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
    $view['channel']['title'] = 'Modificări pe ' . Config::SITE_NAME;
    $view['channel']['link'] = url_absolute(url_changes());
    $view['channel']['description'] = 'Ultimele modificări din wiki-ul ' . Config::SITE_NAME;
    $view['channel']['language'] = 'ro-ro';
    $view['channel']['copyright'] =
      sprintf('%s-%s %s', Config::COPYRIGHT_FIRST_YEAR, date('Y'),
              Config::COPYRIGHT_OWNER);

    $view['item'] = array();

    foreach ($revisions as $rev) {
      $item = array();
      $created = ($rev["timestamp"] == $rev["creation_timestamp"]);
      $item['title'] = sprintf("%s (%s) %s de %s",
                               $rev['title'], $rev['name'], $created ? "creat" : "modificat", $rev['user_name']);

      $userlink = format_user_tiny($rev['user_name']);
      $pagelink = format_link(url_textblock($rev['name'], true), "{$rev['title']} ({$rev['name']})");
      $diffurl = url_textblock_diff($rev['name'], $rev['revision_id'] - 1, $rev['revision_id']);
      $difflink = (!$created) ? " (".format_link($diffurl, "modificari").")" : "";
      $tstamp = format_date($rev['timestamp']);
      $created_or_changed = $created ? "creată" : "modificată";
      $item['description'] = "La data de $tstamp pagina $pagelink a fost $created_or_changed de $userlink$difflink.";

      $item['guid'] = sha1($rev['name'] . $rev['timestamp']);
      $item['link'] = url_absolute(url_textblock($rev['name']));

      $view['item'][] = $item;
    }
    execute_view_die('views/rss.php', $view);
  } else {
    $view = array();
    $view['title'] = 'Modificări pe ' . Config::SITE_NAME;
    $view['page_name'] = 'changes';
    $view['revisions'] = $revisions;
    execute_view_die('views/changes.php', $view);
  }
}
