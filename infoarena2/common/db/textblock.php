<?
require_once("db.php");

// Textblock-related db functions.
//
// FIXME: this is beyond retarded, refactor mercilessly.

// Add a new revision
// FIXME: hash parameter?
function textblock_add_revision($name, $title, $content, $user_id, $security, $timestamp = null) {
    // copy current version to revision table
    $query = sprintf("INSERT INTO ia_textblock_revision
                        SELECT *
                        FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'",
                     db_escape($name));
    db_query($query);

    // replace current version
    $query = sprintf("DELETE FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'
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
        $field_list .= ", `username` as `user_name`, `full_name` as `user_fullname`";
        $join = "LEFT JOIN ia_user ON `user_id` = `ia_user`.`id`";
    } else {
        $join = "";
    }

    // prefix or page_name
    if (getattr($options, 'page_name') === null) {
        log_assert(is_string($options['prefix']));
        $where = sprintf("WHERE LCASE(`name`) LIKE '%s%%'", db_escape(strtolower($options['prefix'])));
    } else {
        $where = sprintf("WHERE LCASE(`name`) = '%s'", db_escape(strtolower($options['page_name'])));
    }

    // When doing a history query.
    if (getattr($options, 'history', false) == true) {
        assert(is_whole_number($options['limit_start']));
        assert(is_whole_number($options['limit_count']));
        $query = sprintf("SELECT $field_list FROM ia_textblock $join $where
                          UNION SELECT $field_list FROM ia_textblock_revision $join $where
                          ORDER BY `timestamp` LIMIT %d, %d",
                          $options['limit_start'], $options['limit_count']);
    } else {
        $query = sprintf("SELECT $field_list FROM ia_textblock $join $where");
    }
    //log_print("QUERY: " . $query);
    return db_fetch_all($query);
}

// Get a certain revision of a textblock. Parameters:
//  $name:      Textblock name.
//  $rev_num:   Revision number. Latest if null(default).
function textblock_get_revision($name, $rev_num = null)
{
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
function textblock_get_list_by_prefix($prefix, $content = false, $username = false) {
    return textblock_complex_query(array(
            'content' => $content,
            'username' => $username,
            'prefix' => $prefix,
    ));
}

// Count revisions for a certain textblock.
// FIXME: undefined if it doesn't exist.
function textblock_get_revision_count($name) {
    global $dbLink;
    $query = sprintf("SELECT COUNT(*) AS `cnt` FROM ia_textblock_revision
                      WHERE LCASE(`name`) = '%s'",
                    db_escape($name));
    $row = db_fetch($query);
    return $row['cnt'] + 1;
}

// Grep through textblocks. This is mostly a hack needed for macro_grep.php
function textblock_grep($substr, $page) {
    $query = sprintf("SELECT `name`, `title`, `timestamp`, `user_id`, `security`
                      FROM ia_textblock
                      WHERE `name` LIKE '%s' AND `text` LIKE '%s'
                      ORDER BY `name`",
                      db_escape($page), db_escape($substr));
    return db_fetch_all($query);
}

// Delete a certain page, including all revisions.
// WARNING: This is irreversible.
function textblock_delete($page) {
    $pageesc = db_escape($page);
    db_query("DELETE FROM `ia_textblock` WHERE `name` = '$pageesc'");
    db_query("DELETE FROM `ia_textblock_revision` WHERE `name` = '$pageesc'");
}

?>
