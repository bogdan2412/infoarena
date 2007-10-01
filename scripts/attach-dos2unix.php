#! /usr/bin/env php
<?php
// This script is used to repair bad filenames in the IA_ROOT_DIR/attach folder.
// Previously infoarena lowercased the filename for any attachment making "test" and "Test" 
// having the same file in the attach folder. This script tries to fix this behavior.

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR."common/attachment.php");
require_once(IA_ROOT_DIR."common/db/attachment.php");

ini_set("memory_limit", "128M");

function attachment_get_bad_filepath($attach) {
    assert(is_array($attach));
    return IA_ROOT_DIR.'attach/'.
            strtolower(preg_replace('/[^a-z0-9\.\-_]/i', '_', $attach['page'])) . '_' .
            strtolower(preg_replace('/[^a-z0-9\.\-_]/i', '_', $attach['name'])) . '_' .
            $attach['id'];
}

db_connect();
$query = "SELECT * FROM ia_file;";
$attachments = db_fetch_all($query);
$fixed = 0;

log_print("Exista ".count($attachments)." atasamente...");
foreach ($attachments as $attach) {
    if (!is_textfile($attach['mime_type'])) {
        continue;
    }
    log_print('Repar '.$attach['page'].'\\'.$attach['name']);
    dos_to_unix(attachment_get_filepath($attach));
    $fixed++;
}
log_print("S-au reparat ".$fixed." atasamente!");
