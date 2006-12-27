<?

require_once(IA_ROOT."common/db/db.php");
require_once(IA_ROOT."common/db/attachment.php");
require_once(IA_ROOT."common/security.php");
require_once(IA_ROOT."common/textblock.php");

// Textblock-related db functions.
//
// FIXME: this is beyond retarded, refactor mercilessly.

// Add a new revision
// FIXME: hash parameter?
function textblock_add_revision($name, $title, $content, $user_id, $security = "public", $timestamp = null) {
    $name = normalize_page_name($name);
    $tb = array(
            'name' => $name,
            'title' => $title,
            'text' => $content,
            'user_id' => $user_id,
            'security' => $security,
            'timestamp' => $timestamp,
    );
    log_assert_valid(textblock_validate($tb));

    // copy current version to revision table
    $query = sprintf("INSERT INTO ia_textblock_revision
                        SELECT *
                        FROM ia_textblock
                      WHERE `name` = '%s'",
                     db_escape($name));
    db_query($query);

    // replace current version
    $query = sprintf("DELETE FROM ia_textblock
                      WHERE `name` = '%s'
                      LIMIT 1",
                     db_escape($name));
    db_query($query);

    $timestampVal = is_null($timestamp) ? "NOW()" : "'".db_escape($timestamp)."'";
    $query = sprintf("INSERT INTO ia_textblock
                        (name, `text`, `title`, `timestamp`, `user_id`, `security`)
                      VALUES ('%s', '%s', '%s', %s, '%s', '%s')",
                     db_escape($name), db_escape($content),
                     db_escape($title), $timestampVal,
                     db_escape($user_id), db_escape($security));
    return db_query($query);
}

// This is the function called by most query functions.
function textblock_complex_query($options)
{
    //log_print_r($options);

    $field_list = "`name`, `title`, `timestamp`, `security`, `user_id`";

    // Select content.
    if (getattr($options, 'content', false) == true) {
        $field_list .= ", `text`";
    }

    // Add a join for username.
    if (getattr($options, 'username', false) == true) {
        $field_list .= ", `username` as `user_name`, `full_name` as `user_fullname`, rating_cache";
        $join = "LEFT JOIN ia_user ON `user_id` = `ia_user`.`id`";
    } else {
        $join = "";
    }

    // prefix or page_name
    if (getattr($options, 'page_name') === null) {
        log_assert(is_string($options['prefix']));
        $where = sprintf("WHERE `name` LIKE '%s%%'", db_escape(strtolower($options['prefix'])));
    } else {
        $where = sprintf("WHERE `name` = '%s'", db_escape(strtolower($options['page_name'])));
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
        $query = sprintf("SELECT $field_list FROM ia_textblock $join %s
                          UNION ALL SELECT $field_list FROM ia_textblock_revision $join %s
                          ORDER BY `timestamp` %s LIMIT %d, %d",
                          $where, $where, $order, $options['limit_start'], $options['limit_count']);
    } else {
        $query = "SELECT $field_list FROM ia_textblock $join $where";
    }
    //log_print("QUERY: " . $query);
    return db_fetch_all($query);
}

// Get a certain revision of a textblock. Parameters:
//  $name:      Textblock name.
//  $rev_num:   Revision number. Latest if null(default).
function textblock_get_revision($name, $rev_num = null)
{
    $name = normalize_page_name($name);
    log_assert(is_normal_page_name($name));
    if (is_null($rev_num)) {
        // Quick latest revision query.
        $res = textblock_complex_query(array(
                'page_name' => $name,
                'content' => true,
                'username' => false,
        ));
    } else {
        $res = textblock_complex_query(array(
                'page_name' => $name,
                'content' => true,
                'username' => false,
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
function textblock_get_by_prefix($prefix, $content = false, $username = false) {
    return textblock_complex_query(array(
            'content' => $content,
            'username' => $username,
            'prefix' => $prefix,
    ));
}

// Get all textblocks(without content) with a certain prefix).
// Ordered by name.
function textblock_get_changes($prefix, $content = false, $username = true, $count = 50) {
    return textblock_complex_query(array(
            'content' => $content,
            'username' => $username,
            'prefix' => $prefix,
            'history' => true,
            'order' => 'desc',
            'limit_start' => 0,
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
function textblock_grep($substr, $page) {
    $query = sprintf("SELECT `name`, `title`, `timestamp`, `user_id`, `security`
                      FROM ia_textblock
                      WHERE `name` LIKE '%s' AND
                            (`text` LIKE '%s' OR `title` LIKE '%s')
                      ORDER BY `name`",
                      db_escape($page), db_escape($substr), db_escape($substr));
    return db_fetch_all($query);
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
            return false;
        }
    }
    db_query("DELETE FROM `ia_textblock_revision` WHERE `name` = '$pageesc'");
    db_query("DELETE FROM `ia_textblock` WHERE `name` = '$pageesc'");
    if (db_affected_rows() != 1) {
        return true;
    }
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
                      WHERE LCASE(`page`) = LCASE('%s')",
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

?>
