<?php

require_once(Config::ROOT . "www/format/format.php");
require_once(Config::ROOT . "www/format/list.php");
require_once(Config::ROOT . "www/url.php");

function round_edit_tabs($round_id, $active) {
    $tabs = [];

    $round = round_get($round_id);

    if (Identity::ownsRound($round)) {
      $url = url_round_edit($round_id);
      $tabs['edit'] = format_link($url, 'Pagină');

      $url = url_round_edit_params($round_id);
      $tabs['round-edit-params'] = format_link($url, 'Parametri');

      $url = url_round_edit_task_order($round_id);
      $tabs['round-edit-task-order'] = format_link($url, 'Ordine probleme');
    }

    $tabs[$active] = array($tabs[$active], array('class' => 'active'));

    return format_ul($tabs, 'htabs');
}

?>
