<?php

require_once("db.php");

/**
 * Attachment functions.
 */

// Get attachment by name
function attachment_get($name, $page) {
    $query = sprintf("SELECT *, DATE_FORMAT(`timestamp`, '%%Y-%%M-%%D %%h:%%i:%%s')
                      FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                     db_escape($name), db_escape($page));
    return db_fetch($query);
}

function attachment_get_by_id($id) {
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE `id` = '%s'",
                     db_escape($id));
    return db_fetch($query);
}

function attachment_update($id, $name, $size, $mime_type, $page, $user_id) {
    $query = sprintf("UPDATE ia_file
                      SET size = '%s', user_id ='%s', `timestamp` = NOW(),
                          mime_type = '%s'
                      WHERE id = %s",
                     db_escape($size), db_escape($user_id),
                     db_escape($mime_type), db_escape($id));
    return db_query($query);
}

function attachment_insert($name, $size, $mime_type, $page, $user_id) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_file
                        (`name`, page, `size`, mime_type, user_id, `timestamp`)
                      VALUES ('%s', '%s', '%s', '%s', '%s', NOW())",
                     db_escape($name), db_escape($page),
                     db_escape($size), db_escape($mime_type),
                     db_escape($user_id));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function attachment_delete($id) {
    global $dbLink;
    $query = sprintf("DELETE FROM ia_file WHERE `id` = %s", db_escape($id));
    return db_query($query);
}

// Obtain list with all attachments matching name $name and belonging
// to page $page.
//
// You may use % as a wildcard
function attachment_get_all($page, $name='%') {
    $query = sprintf("SELECT *
                      FROM ia_file
                      LEFT JOIN ia_user ON ia_file.user_id = ia_user.id
                      WHERE ia_file.page LIKE '%s'
                            AND ia_file.`name` LIKE '%s'
                      ORDER BY ia_file.`name`, ia_file.`timestamp` DESC",
                     db_escape($page), db_escape($name));
    return db_fetch_all($query);
}

?>
