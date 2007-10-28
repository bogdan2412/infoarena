<?php

require_once(IA_ROOT_DIR."common/db/tags.php");

function blog_get_range($tag_name, $start, $range) {
    if (is_tag_name($tag_name)) {
        $tag_id = tag_get_id($tag_name);
        if (is_null($tag_id)) {
            $tag_id = -1;
        }
        $where = tag_build_where("textblock", array($tag_id));
    } else {
        $where = "TRUE";
    }
    $query = sprintf("SELECT * FROM ia_textblock 
                      WHERE %s AND name LIKE 'blog/%%' 
                      ORDER BY ia_textblock.creation_timestamp DESC
                      LIMIT %s, %s", 
                     $where, db_quote((int)$start), db_quote((int)$range));
    return db_fetch_all($query);
}

function blog_count($tag_name) {
    if (is_tag_name($tag_name)) {
        $tag_id = tag_get_id($tag_name);
        if (is_null($tag_id)) {
            $tag_id = -1;
        }
        $where = tag_build_where("textblock", array($tag_id));
    } else {
        $where = "TRUE";
    }
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_textblock WHERE %s AND
                      name LIKE 'blog/%%'", $where);
    $result = db_fetch($query);
    return $result['cnt'];
}

function blog_get_tags() {
    $query = "SELECT name, 
                     (SELECT COUNT(*) FROM ia_textblock_tags WHERE tag_id = id AND textblock_id LIKE 'blog/%%') AS cnt
              FROM ia_tags WHERE id IN 
              (SELECT DISTINCT tag_id FROM ia_textblock_tags WHERE textblock_id LIKE 'blog/%%')
              ORDER BY name";
    return db_fetch_all($query);
}
?>
