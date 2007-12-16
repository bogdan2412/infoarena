#! /usr/bin/env php
<?php
// This script is used to repair bad filenames in the IA_ROOT_DIR/attach folder.
// Previously infoarena lowercased the filename for any attachment making "test" and "Test" 
// having the same file in the attach folder. This script tries to fix this behavior.

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR."common/db/attachment.php");

ini_set("memory_limit", "128M");

db_connect();
$query = "SELECT * FROM ia_file;";
$attachments = db_fetch_all($query);
$fixed = $errors = 0;

log_print("Exista ".count($attachments)." atasamente...");
foreach ($attachments as $attach) {
    log_print('Verific '.$attach['page'].'\\'.$attach['name']);
    $name = attachment_get_filepath($attach);
    if (!file_exists($name)) {
        log_warn('Fisierul '.$name.' nu exista!');
        continue;
    }
    $true_size = filesize($name);
    if ($attach['size'] != $true_size) {
        log_print('Repar '.$attach['page'].'\\'.$attach['name']);
        $query = sprintf("UPDATE ia_file SET size = %s WHERE id = %s", $attach['size'], $attach['id']);
        $fixed++;
    }
}
log_print("S-au reparat ".$fixed." atasamente!");
