<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/attachment.php");
require_once(IA_ROOT_DIR."common/cache.php");

// Add $attachment to cache if not null and return $attachment.
function _attachment_cache_add($attachment) {
    if (!is_null($attachment)) {
        log_assert_valid(attachment_validate($attachment));
        mem_cache_set("attachment-by-id:{$attachment['id']}", $attachment);
        mem_cache_set("attachment-by-name:".
                $attachment['page'] . '$' . $attachment['name'], $attachment);
    }
    return $attachment;
}

function _attachment_cache_delete($att) {
    mem_cache_delete("attachment-by-id:{$att['id']}");
    mem_cache_delete("attachment-by-name:".$att['page']."$".$att['name']);
}

// Get attachment by name
function attachment_get($name, $page) {
    log_assert(is_attachment_name($name));
    log_assert(is_page_name($page));
    $page = normalize_page_name($page);

    if (($res = mem_cache_get("attachment-by-name:$page$$name")) !== false) {
        return $res;
    }

    $query = sprintf("SELECT *, DATE_FORMAT(`timestamp`, '%%Y-%%M-%%D %%h:%%i:%%s')
                      FROM ia_file
                      WHERE BINARY `name` = '%s' AND
                            `page` = '%s'",
                     db_escape($name), db_escape($page));

    // This way nulls (missing attachments) get cached too.
    $attachment = db_fetch($query);
    if ($attachment != null) {
        return _attachment_cache_add($attachment);
    } else {
        return mem_cache_set("attachment-by-name:$page$$name", null);
    }
}

function attachment_get_by_id($id) {
    if (($res = mem_cache_get("attachment-by-id:$id")) !== false) {
        return $res;
    }

    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE `id` = '%s'",
                     db_escape($id));

    // This way nulls (missing attachments) get cached too.
    $attachment = db_fetch($query);
    if ($attachment != null) {
        return _attachment_cache_add($attachment);
    } else {
        return mem_cache_set("attachment-by-id:$id", null);
    }
}

// Update an attachment. FIXME: hash args.
function attachment_update($id, $name, $size, $mime_type, $page, $user_id,
        $remote_ip_info) {
    $attachment = array(
            'id' => $id,
            'name' => $name,
            'size' => $size,
            'mime_type' => $mime_type,
            'page' => $page,
            'user_id' => $user_id,
            'timestamp' => db_date_format(),
            'remote_ip_info' => $remote_ip_info,
    );

    db_update('ia_file', $attachment, '`id` = '.db_quote($id));

    _attachment_cache_add($attachment);
}

// Inserts an attachment in the db
function attachment_insert($name, $size, $mime_type, $page, $user_id,
        $remote_ip_info) {
    $attachment = array(
            'name' => $name,
            'size' => $size,
            'mime_type' => $mime_type,
            'page' => $page,
            'user_id' => $user_id,
            'timestamp' => db_date_format(),
            'remote_ip_info' => $remote_ip_info,
    );

    db_insert('ia_file', $attachment);
    $attachment['id'] = db_insert_id();
    _attachment_cache_add($attachment);

    return $attachment['id'];
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
    _attachment_cache_delete($attach);
    if (!@unlink(attachment_get_filepath($attach))) {
        return false;
    }
    return true;
}

function attachment_rename($attach, $new_name) {
    log_assert_valid(attachment_validate($attach));

    db_query(sprintf("UPDATE ia_file SET `name` = \"%s\" WHERE `id` = %s",
            db_escape($new_name), db_escape($attach['id'])));
    if (db_affected_rows() != 1) {
        return false;
    }

    _attachment_cache_delete($attach);
    $new_attach = $attach;
    $new_attach['name'] = $new_name;
    _attachment_cache_add($new_attach);

    if (!@rename(attachment_get_filepath($attach), attachment_get_filepath($new_attach))) {
        return false;
    }
    return true;
}

// Delete by id. Just in case you want to do an extra query.
function attachment_delete_by_id($attid) {
    attachment_delete(attachment_get_by_id($attid));
}

// Obtain list with all attachments matching name $name and belonging
// to page $page.
//
// You may use % as a wildcard
function attachment_get_all($page, $name='%', $start = 0, $count = 99999999) {
    assert(is_whole_number($start));
    assert(is_whole_number($count));
    $query = sprintf("SELECT ia_file.*, ia_user.username, ia_user.full_name as user_fullname
                      FROM ia_file
                      LEFT JOIN ia_user ON ia_user.id = ia_file.user_id
                      WHERE ia_file.page LIKE '%s' AND ia_file.`name` LIKE '%s'
                      ORDER BY ia_file.`timestamp` DESC, ia_file.`name`
                      LIMIT %d, %d",
                     db_escape($page), db_escape($name), $start, $count);
    return db_fetch_all($query);
}

// _count for the above.
function attachment_get_count($page, $name='%', $start = 0, $count = 99999999) {
    assert(is_whole_number($start));
    assert(is_whole_number($count));
    $query = sprintf("SELECT COUNT(*)
                      FROM ia_file
                      LEFT JOIN ia_user ON ia_user.id = ia_file.user_id
                      WHERE ia_file.page LIKE '%s' AND ia_file.`name` LIKE '%s'
                      ORDER BY ia_file.`timestamp` DESC, ia_file.`name`
                      LIMIT %d, %d",
                     db_escape($page), db_escape($name), $start, $count);
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
    return IA_ROOT_DIR.'attach/'.
            strtolower(preg_replace('/[^a-z0-9\.\-_]/i', '_', $attach['page'])) . '_' .
            preg_replace('/[^a-z0-9\.\-_]/i', '_', $attach['name']) . '_' .
            $attach['id'];
}

?>
