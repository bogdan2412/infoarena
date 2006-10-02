<?
require_once("db.php");

// Textblock-related db functions.

// Call this function to add a new revision.
function textblock_add_revision($name, $title, $content, $user_id) {
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
    $query = sprintf("INSERT INTO ia_textblock
                        (name, `text`, `title`, `timestamp`, user_id)
                      VALUES ('%s', '%s', '%s', NOW(), '%s')",
                     db_escape($name), db_escape($content),
                     db_escape($title), db_escape($user_id));
    return db_query($query);
}

// Get a certain revision of a textblock.
function textblock_get_revision($name, $rev_num = null) {
    global $dbLink;
    if (is_null($rev_num)) {
        $query = sprintf("SELECT *
                          FROM ia_textblock
                          WHERE LCASE(`name`) = '%s'",
                         db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
    }
    else {
        $query = sprintf("SELECT *
                          FROM ia_textblock_revision
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

// Returns all all revisions of a textblock.
function textblock_get_revisions($name) {
    $query = sprintf("SELECT *
                      FROM ia_textblock_revision WHERE
                      LCASE(`name`) = '%s'
                      ORDER BY `timestamp`",
                     db_escape($name));
    return db_fetch_all($query);
}

// returns textblocks without the actual content 
function textblock_get_revisions_without_content($name) {
    $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                      FROM ia_textblock_revision
                        LEFT JOIN ia_user ON
                            ia_textblock_revision.user_id = ia_user.id
                      WHERE LCASE(`name`) = '%s'
                      ORDER BY `timestamp`",
                     db_escape($name));
    return db_fetch_all($query);
}

// Get a certain revision without the actual content.
function textblock_get_revision_without_content($name, $rev_num = null) {
    global $dbLink;
    if (is_null($rev_num)) {
        $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                          FROM ia_textblock
                          LEFT JOIN ia_user ON
                            ia_textblock.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`", db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
}
    else {
        $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                          FROM ia_textblock_revision LEFT JOIN ia_user ON
                          ia_textblock_revision.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

// Get all revisions and join with user infos.
function textblock_get_revisions_with_username($name) {
    $query = sprintf("SELECT * FROM ia_textblock_revision LEFT JOIN ia_user
                      ON ia_textblock_revision.user_id = ia_user.id
                      WHERE LCASE(`name`)= '%s' ORDER BY `timestamp`",
                    db_escape($name));
    return db_fetch_all($query);
}

function textblock_get_revision_with_username($name, $rev_num = null) {
    global $dbLink;
    if (is_null($rev_num)) {
        $query = sprintf("SELECT *
                          FROM ia_textblock
                          LEFT JOIN ia_user ON
                            ia_textblock.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`", db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
    }
    else {
        $query = sprintf("SELECT *
                          FROM ia_textblock_revision LEFT JOIN ia_user ON
                          ia_textblock_revision.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

// Count revisions.
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
function textblock_get_names($prefix) {
   $query = sprintf("SELECT `name`, title, user_id, `timestamp`
                     FROM ia_textblock
                     WHERE LCASE(`name`) LIKE '%s%%'
                     ORDER BY `name`",
                    db_escape($prefix));
    return db_fetch_all($query);
}

// No fucking idea.
function textblock_get_names_with_user($prefix) {
    $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                      FROM ia_textblock
                        LEFT JOIN ia_user ON ia_textblock.user_id = ia_user.id
                      WHERE LCASE(`name`) LIKE '%s%%'
                      ORDER BY `name`",
                     db_escape($prefix));
    return db_fetch_all($query);
}

?>
