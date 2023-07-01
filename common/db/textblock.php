<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/tags.php");
require_once(IA_ROOT_DIR."common/db/attachment.php");
require_once(IA_ROOT_DIR."common/security.php");
require_once(IA_ROOT_DIR."common/textblock.php");
require_once(IA_ROOT_DIR."common/common.php");

// Textblock-related db functions.
//
// FIXME: this is beyond retarded, refactor mercilessly.

// Add a new revision
// FIXME: hash parameter?
function textblock_add_revision(
        $name, $title, $content, $user_id, $security = "public",
        $timestamp = null, $creation_timestamp = null,
        $remote_ip_info = null) {
    $name = normalize_page_name($name);

    $content = text_cedilla_to_comma_below_st($content);

    $tb = array(
        'name' => $name,
        'title' => $title,
        'text' => $content,
        'user_id' => $user_id,
        'security' => $security,
        'timestamp' => $timestamp,
        'creation_timestamp' => $creation_timestamp,
        'remote_ip_info' => $remote_ip_info,
    );
    log_assert_valid(textblock_validate($tb));

    // get current revision
    $query = sprintf("SELECT * FROM ia_textblock
                      WHERE `name` = '%s'",
                     db_escape($name));
    $current_revision = db_fetch($query);

    if ($current_revision) {
        // copy current version to revision table
        db_insert('ia_textblock_revision', $current_revision);

        // replace current version
        $query = sprintf("DELETE FROM ia_textblock
                          WHERE `name` = '%s'
                          LIMIT 1",
                         db_escape($name));
        db_query($query);
    }

    // Evil.
    if ($creation_timestamp === null) {
        $creation_timestamp = db_date_format();
    } else {
        log_assert(is_db_date($creation_timestamp), "Invalid timestamp");
    }
    if ($timestamp === null) {
        $timestamp = db_date_format();
    } else {
        log_assert(is_db_date($timestamp), "Invalid timestamp");
    }
    $query = sprintf("INSERT INTO ia_textblock
            (name, `text`, `title`, `creation_timestamp`,
                    `timestamp`, `user_id`, `security`,
                    `remote_ip_info`)
            VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', %s)",
            db_escape($name), db_escape($content), db_escape($title),
            db_escape($creation_timestamp), db_escape($timestamp),
            db_escape($user_id), db_escape($security),
            db_quote($remote_ip_info));
    return db_query($query);
}

// Delete $revision from database
// The revision is identified by name and timestamp
// $curr is true if $revision is the current revision and false otherwise
function textblock_delete_revision($revision, $curr)
{
    if ($curr == false) {
        $name = $revision['name'];
        $timestamp = $revision['timestamp'];
        $query = "DELETE FROM `ia_textblock_revision`
            WHERE `name` = ".db_quote($name)." &&
                `timestamp` = ".db_quote($timestamp);
        db_query($query);
    } else {
        $name = $revision['name'];
        $query = "REPLACE INTO `ia_textblock`
                    (SELECT * FROM `ia_textblock_revision`
                    WHERE `name` = ".db_quote($name)."
                    ORDER BY `timestamp` DESC LIMIT 1)";
        db_query($query);

        //delete last_rev from ia_textblock_revision
        $query = "DELETE FROM `ia_textblock_revision`
                WHERE `name` = ".db_quote($name)."
                ORDER BY `timestamp` DESC LIMIT 1";
        db_query($query);
    }
}

// This is the function called by most query functions.
function textblock_complex_query($options) {
    $field_list = '`name`, `title`, `creation_timestamp`, `timestamp`, ' .
                  '`security`, `user_id`, `remote_ip_info`';

    // Select content.
    if (getattr($options, 'content', false) == true) {
        $field_list .= ', `text`';
    }

    // Add a join for username.
    if (getattr($options, 'username', false) == true) {
        $field_list .= ', `username` as `user_name`, ' .
                       '`full_name` as `user_fullname`, `rating_cache`';
        $join = 'LEFT JOIN ia_user ON `user_id` = `ia_user`.`id`';
    } else {
        $join = '';
    }

    // prefix or page_name
    if (getattr($options, 'page_name') !== null) {
        $where = sprintf('WHERE `name` = "%s"',
                         db_escape(strtolower($options['page_name'])));
    } else if (getattr($options, 'prefix')) {
        log_assert(is_string($options['prefix']));
        $where = sprintf('WHERE `name` LIKE "%s%%"',
                         db_escape(strtolower($options['prefix'])));
    } else {
        $where = '';
    }

    if (strtolower(getattr($options, 'order') == 'desc')) {
        $order = 'DESC';
    } else {
        $order = 'ASC';
    }

    // When doing a history query.
    if (getattr($options, 'history', false) == true) {
        log_assert(is_whole_number($options['limit_start']));
        log_assert(is_whole_number($options['limit_count']));
        $order_limit = sprintf(
            "ORDER BY `timestamp` %s LIMIT %d, %d",
            $order, $options['limit_start'], $options['limit_count']);
        $common = "$join $where";
        if ($options['limit_start'] == 0) {
            $common .= " " . $order_limit;
        }
        $query =
            "(SELECT $field_list FROM ia_textblock $common) UNION ALL " .
            "(SELECT $field_list FROM ia_textblock_revision $common) " .
            $order_limit;
    } else {
        $query =
            "SELECT $field_list FROM ia_textblock " .
            "$join $where ORDER BY ia_textblock.`creation_timestamp` $order";
    }
    return db_fetch_all($query);
}

// Get a certain revision of a textblock. Parameters:
//  $name:      Textblock name.
//  $rev_num:   Revision number. Latest if null(default).
//  $username:  Get user name info.
function textblock_get_revision($name, $rev_num = null, $username = false)
{
    $name = normalize_page_name($name);
    log_assert(is_normal_page_name($name));
    if (is_null($rev_num)) {
        // Quick latest revision query.
        $res = textblock_complex_query(array(
                'page_name' => $name,
                'content' => true,
                'username' => $username,
        ));
    } else {
        $res = textblock_complex_query(array(
                'page_name' => $name,
                'content' => true,
                'username' => $username,
                'history' => true,
                'limit_start' => (int)$rev_num - 1,
                'limit_count' => 1,
        ));
    }
    return array_key_exists(0, $res) ? $res[0] : null;
}

// Get all revisions of a text_block.
// $name:       The textblock name.
// $content:    If true also get content. Defaults to false.
// $username:   If true join for username. Defaults to true.
function textblock_get_revision_list($name, $content = false, $username = true,
        $start = 1, $count = 99999999) {
    $name = normalize_page_name($name);
    log_assert(is_normal_page_name($name));
    return textblock_complex_query(array(
            'content' => $content,
            'username' => $username,
            'page_name' => $name,
            'history' => true,
            'limit_start' => $start - 1,
            'limit_count' => $count,
    ));
}

// Get all textblocks(without content) with a certain prefix).
// Ordered by name.
function textblock_get_by_prefix($prefix, $content = false, $username = false,
        $order = 'asc') {
    return textblock_complex_query(array(
            'content' => $content,
            'username' => $username,
            'prefix' => $prefix,
            'order' => $order,
    ));
}

// Get all textblocks(without content) with a certain prefix.
// Ordered by name.
function textblock_get_changes($prefix, $content = false, $username = true,
                               $offset = 0, $count = 50) {
    return textblock_complex_query(array(
            'content' => $content,
            'username' => $username,
            'prefix' => $prefix,
            'history' => true,
            'order' => 'desc',
            'limit_start' => $offset,
            'limit_count' => $count,
    ));
}

// Count revisions for a certain textblock.
// FIXME: undefined if it doesn't exist.
function textblock_get_revision_count($name) {
    $name = normalize_page_name($name);
    log_assert(is_normal_page_name($name));

    $query = sprintf("SELECT COUNT(*) AS `cnt` FROM ia_textblock_revision
                      WHERE `name` = '%s'",
                    db_escape(strtolower($name)));
    $row = db_fetch($query);
    return $row['cnt'] + 1;
}

// Grep through textblocks. This is mostly a hack needed for macro_grep.php
// Also used for round deletion
function textblock_grep($substr, $page, $regexp = false, $offset = null, $count = null) {
    if (!$regexp) {
        $compare = "LIKE";
    } else {
        $compare = "REGEXP";
    }
    $query = sprintf("SELECT `name`, `title`, `creation_timestamp`, `timestamp`,
                            `user_id`, `security`, `remote_ip_info`
                      FROM ia_textblock
                      WHERE `name` LIKE '%s' AND
                            (`text` $compare '%s' OR `title` $compare '%s')
                      ORDER BY `name`",
                      db_escape($page), db_escape($substr), db_escape($substr));

    if (is_whole_number($offset) && is_whole_number($count)) {
        $query .= sprintf(" LIMIT %s, %s",
                          db_escape($offset), db_escape($count));
    }
    return db_fetch_all($query);
}

function textblock_grep_count($substr, $page, $regexp = false) {
    if (!$regexp) {
        $compare = "LIKE";
    } else {
        $compare = "REGEXP";
    }
    $query = sprintf("SELECT COUNT(*) as `cnt`
                      FROM ia_textblock
                      WHERE `name` LIKE '%s' AND
                            (`text` $compare '%s' OR `title` $compare '%s')",
                      db_escape($page), db_escape($substr), db_escape($substr));
    return db_fetch($query);
}

// Delete a certain page, including all revisions and attachments.
// WARNING: This is irreversible.
function textblock_delete($page_name) {
    $page_name = normalize_page_name($page_name);
    log_assert(is_normal_page_name($page_name));

    $pageesc = db_escape($page_name);
    $atts = attachment_get_all($page_name);
    foreach ($atts as $att) {
        if (!attachment_delete($att)) {
            log_warn('Could not delete attachment from '.$page_name);
            return false;
        }
    }
    tag_clear('textblock', $page_name);

    db_query("DELETE FROM `ia_textblock_revision` WHERE `name` = '$pageesc'");
    db_query("DELETE FROM `ia_textblock` WHERE `name` = '$pageesc'");

    return (db_affected_rows() != 0);
}

// Move a page from old_name to new_name.
// Also drags attachments.
function textblock_move($old_name, $new_name) {
    $old_name = normalize_page_name($old_name);
    $new_name = normalize_page_name($new_name);
    log_assert(is_normal_page_name($old_name));
    log_assert(is_normal_page_name($new_name));
    //log_print("Moving textblock $old_name to $new_name");

    // Move current version.
    $query = sprintf("UPDATE `ia_textblock`
                      SET `name` = '%s'
                      WHERE `name` = '%s'",
                      db_escape($new_name), db_escape($old_name));
    db_query($query);

    // Move history.
    $query = sprintf("UPDATE `ia_textblock_revision`
                      SET `name` = '%s'
                      WHERE `name` = '%s'",
                      db_escape($new_name), db_escape($old_name));
    db_query($query);

    // Get a list of attachments.
    $files = attachment_get_all($old_name);

    // Move attachments in db.
    $query = sprintf("UPDATE `ia_file`
                      SET `page` = '%s'
                      WHERE `page` = '%s'",
                      db_escape($new_name), db_escape($old_name));
    db_query($query);

    // Move attachments on disk. Ooops.
    foreach ($files as $file) {
        $old_filename = attachment_get_filepath($file);
        $file['page'] = $new_name;
        $new_filename = attachment_get_filepath($file);

        if (!@rename($old_filename, $new_filename)) {
            log_error("Failed moving attachment from $old_filename to $new_filename");
        }
    }
}

// Copy a textblock to new_name.
// Also copies attachments.
function textblock_copy($old_textblock, $new_name, $user_id, $remote_ip_info) {
    log_assert(!textblock_validate($old_textblock));
    $new_name = normalize_page_name($new_name);
    log_assert(is_normal_page_name($new_name));

    $new_textblock = $old_textblock;
    $new_textblock['name'] = $new_name;
    $new_textblock['user_id'] = $user_id;
    $new_textblock['creation_timestamp'] = null;
    // Keep creation_timestamp correct when textblock with new name already exists
    $aux = textblock_get_revision($new_name);
    if ($aux) {
        $new_textblock['creation_timestamp'] = $aux['creation_timestamp'];
    }
    textblock_add_revision($new_textblock['name'], $new_textblock['title'],
                           $new_textblock['text'], $new_textblock['user_id'],
                           $new_textblock['security'],
                           null, $new_textblock['creation_timestamp'],
                           $remote_ip_info);

    // Get a list of attachments.
    $files = attachment_get_all($old_textblock["name"]);

    // Copy attachments in db and hard drive
    foreach ($files as $file) {
        // Copy in db and get new id
        $new_id = attachment_insert($file['name'], $file['size'],
            $file['mime_type'], $new_name, $user_id, $remote_ip_info);

        // Copy on hard drive
        $old_filename = attachment_get_filepath($file);
        $file['page'] = $new_name;
        $file['id'] = $new_id;
        $new_filename = attachment_get_filepath($file);

        if (!@copy($old_filename, $new_filename)) {
            log_error("Failed copying attachment from $old_filename ".
                "to $new_filename");
        }
    }
}
