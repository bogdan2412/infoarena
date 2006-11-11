<?
require_once("db.php");

// Textblock-related db functions.

// Add a new revision
// FIXME: hash parameter?
function textblock_add_revision($name, $title, $content, $user_id, $timestamp = null) {
    global $dbLink;

    // do a query first
    $query = sprintf("SELECT title, text, user_id
                      FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'",
                     db_escape($name));
    $tmp = db_fetch($query);
    if ($tmp['title'] == $title && $tmp['text'] == $content &&
        $tmp['user_id'] = $user_id) return $tmp;
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
                        (name, `text`, `title`, `timestamp`, user_id)
                      VALUES ('%s', '%s', '%s', %s, '%s')",
                     db_escape($name), db_escape($content),
                     db_escape($title), $timestampVal,
                     db_escape($user_id));
    return db_query($query);
}

// Get a certain revision of a textblock. Paramters:
//  $name:      Textblock name.
//  $rev_num:   Revision number. Latest if null(default).
//  $content:   If true also get content. Default true.
//  $username:  If true also get username (not only user_id). Default true
function textblock_get_revision($name, $rev_num = null, $content = true, $username = true)
{
    // Calculate field list.
    $field_list = "`name`, `title`, `timestamp`, `user_id`";
    if ($content) {
        $field_list .= ", `text`";
    }
    if ($username) {
        $field_list .= ", `username`";
    }

    if (is_null($rev_num)) {
        $query_table = "ia_textblock";
    } else {
        $query_table = "ia_textblock_revision";
    }

    // Add a join for username.
    if ($username) {
        $join = "LEFT JOIN ia_user ON $query_table.user_id = ia_user.id";
    } else {
        $join = "";
    }

    // Build the actual query.
    if (is_null($rev_num)) {
        // Get the latest revision.
        $query = sprintf("SELECT $field_list
                         FROM $query_table
                         $join
                         WHERE LCASE(`name`) = '%s'",
                         db_escape($name));
    } else {
        // Get an older revision.
        $query = sprintf("SELECT $field_list
                         FROM $query_table
                         $join
                         WHERE LCASE(`name`) = '%s'
                         ORDER BY `timestamp`
                         LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
    }
    return db_fetch($query);
}

// Get all revisions of a text_block.
// $name:       The textblock name.
// $content:    If true also get content. Defaults to false.
// $username:   If true join for username. Defaults to true.
function textblock_get_revisions($name, $content = false, $username = true) {
    // Calculate field list.
    $field_list = "`name`, `title`, `timestamp`, `user_id`";
    if ($content) {
        $field_list .= ", `text`";
    }
    if ($username) {
        $field_list .= ", `username`";
    }

    // Add a join for username.
    if ($username) {
        $join = "LEFT JOIN ia_user ON ia_textblock_revision.user_id = ia_user.id";
    } else {
        $join = "";
    }

    // Build query.
    $query = sprintf("SELECT $field_list
                      FROM ia_textblock_revision
                      $join
                      WHERE LCASE(`name`) = '%s'
                      ORDER BY `timestamp`",
                      db_escape($name));
    return db_fetch_all($query);
}

// Count revisions for a certain textblock.
function textblock_get_revision_count($name) {
    global $dbLink;
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock_revision
                      WHERE LCASE(`name`) = '%s'",
                    db_escape($name));
    $row = db_fetch($query);
    return $row['cnt'];
}

// Get all textblocks(without content) with a certain prefix).
// Ordered by name.
function textblock_get_list_by_prefix($prefix, $content = false, $username = false) {
    // Calculate field list.
    $field_list = "`name`, `title`, `timestamp`, `user_id`";
    if ($content) {
        $field_list .= ", `text`";
    }
    if ($username) {
        $field_list .= ", `username`";
    }

    // Add a join for username.
    if ($username) {
        $join = "LEFT JOIN ia_user ON ia_textblock.user_id = ia_user.id";
    } else {
        $join = "";
    }

    $query = sprintf("SELECT $field_list
                      FROM ia_textblock
                      $join
                      WHERE LCASE(`name`) LIKE '%s%%'
                      ORDER BY `name`",
                      db_escape($prefix));
    return db_fetch_all($query);
}

// Grep through textblocks. This is mostly a hack needed for macro_grep.php
function textblock_grep($substr, $page) {
    // Calculate field list.
    $field_list = "`name`, `title`, `timestamp`, `user_id`";

    $query = sprintf("SELECT `name`, `title`, `timestamp`, `user_id`
                      FROM ia_textblock
                      WHERE `name` LIKE '%s' AND `text` LIKE '%s'
                      ORDER BY `name`",
                      db_escape($page), db_escape($substr));
    return db_fetch_all($query);
}

?>
