#! /usr/bin/env php
<?php

// This test looks around the website and catches casual html errors.

require_once(dirname($argv[0]) . '/utilities.php');
require_once(Config::ROOT . 'lib/Wiki.php');

log_print('Passing the entire db through the wiki processor.');
log_print('This might take a while. User pages are skipped.');

// Do the monkey
$field_list = '`name`, `title`, `text`';
$res = db_query("SELECT $field_list FROM ia_textblock ".
                "WHERE `name` NOT LIKE 'utilizator/%'");
while ($tb = db_next_row($res)) {
  log_print("Processing {$tb['name']} ({$tb['title']})");
  Wiki::processText($tb['text']);
}

log_execution_stats();
log_print('Browsing worked');
