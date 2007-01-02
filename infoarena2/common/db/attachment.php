<?php

require_once(IA_ROOT."common/db/db.php");
require_once(IA_ROOT."common/attachment.php");

/**
 * Attachment functions.
 */

// Get attachment by name
function attachment_get($name, $page) {
    log_assert(is_attachment_name($name));
    log_assert(is_page_name($page));
    $page = normalize_page_name($page);
    $name = strtolower($name);
    $query = sprintf("SELECT *, DATE_FORMAT(`timestamp`, '%%Y-%%M-%%D %%h:%%i:%%s')
                      FROM ia_file
                      WHERE `name` = '%s' AND
                            `page` = '%s'",
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
                      SET size = '%s', user_id ='%s', `timestamp` = '%s',
                          mime_type = '%s'
                      WHERE id = %s",
                     db_escape($size), db_escape($user_id),
                     db_escape(db_date_format()),
                     db_escape($mime_type), db_escape($id));
    return db_query($query);
}

// Inserts an attachment in the db
function attachment_insert($name, $size, $mime_type, $page, $user_id) {
    $query = sprintf("INSERT INTO ia_file
                        (`name`, page, `size`, mime_type, user_id, `timestamp`)
                      VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
                     db_escape($name), db_escape($page),
                     db_escape($size), db_escape($mime_type),
                     db_escape($user_id), db_escape(db_date_format()));
    db_query($query);
    return db_insert_id();
}

// Delete an attachment, from both the db and the disk.
// Returns success value.
function attachment_delete($attach) {
    log_assert_valid(attachment_validate($attach));
    db_query(sprintf("DELETE FROM ia_file WHERE `id` = %s",
            db_escape($attach['id'])));
    if (db_affected_rows() != 1) {
        return false;
    }
    if (!@unlink(attachment_get_filepath($attach))) {
        return false;
    }
    return true;
}

// Delete by id. Just in case you want an extra query.
function attachment_delete_by_id($attid) {
    attachment_delete(attachment_get_by_id($attid));
}

// Obtain list with all attachments matching name $name and belonging
// to page $page.
//
// You may use % as a wildcard
function attachment_get_all($page, $name='%', $start = 0, $count = 999999) {
    assert(is_whole_number($start));
    assert(is_whole_number($count));
    $query = sprintf("SELECT ia_file.*, ia_user.username, ia_user.full_name as user_fullname
                      FROM ia_file
                      LEFT JOIN ia_user ON ia_user.id = ia_file.user_id
                      WHERE ia_file.page LIKE '%s' AND ia_file.`name` LIKE '%s'
                      ORDER BY ia_file.`name`, ia_file.`timestamp` DESC
                      LIMIT %d, %d",
                     db_escape($page), db_escape($name), $start, $count);
    return db_fetch_all($query);
}

// _count for the above.
function attachment_get_count($page, $name='%') {
    $query = sprintf("SELECT COUNT(*)
                      FROM ia_file
                      WHERE ia_file.page LIKE '%s' AND ia_file.`name` LIKE '%s'",
                      db_escape($page), db_escape($name));
    return db_query_value($query);
}

// Returns "real file name" (as stored on the file system) for a given
// attachment id.
//
// NOTE: You can't just put this into db.php or any other module shared
// with the judge since it`s dependent on the www server setup.
// FIXME: does this belong here?
function attachment_get_filepath($attach) {
    assert(is_array($attach));
    return IA_ATTACH_DIR .
            preg_replace('/[^a-z0-9\.\-_]/i', '_', $attach['page']) . '_' .
            preg_replace('/[^a-z0-9\.\-_]/i', '_', $attach['name']) . '_' .
            $attach['id'];
}

?>
