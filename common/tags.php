<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/common.php");

function tag_get_id($tag_name) {
    log_assert(is_tag_name($tag_name));
    $query = sprintf("SELECT id FROM ia_tags WHERE name = %s",
                     db_quote($tag_name));
    $result = db_fetch($query);
    return $result['id'];
}

function tag_get_ids($tag_names) {
    foreach ($tag_names as &$name) {
        log_assert(is_tag_name($name));
        $name = db_quote($name);
    }
    $query = sprintf("SELECT id FROM ia_tags WHERE name IN (%s)", implode(", ", $tag_names));
    $result = db_fetch_all($query);
    $ids = array();
    foreach ($result as $row) {
        $ids[] = $row['id'];
    }
    return $ids;
}

function tag_build_where($obj, $tag_names, $parent_table = null) {
    log_assert(is_taggable($obj));
    log_assert(is_array($tag_names));
    $tag_ids = tag_get_ids($tag_names);
    if (is_null($parent_table)) {
        $parent_table = "ia_".db_escape($obj);
    }
    $where = sprintf("(SELECT COUNT(*) FROM ia_%s_tags WHERE %s.id = %s_id ".
                     "AND tag_id IN (%s)) = %d", db_quote($obj), db_escape($obj), 
                      db_escape($parent_table), implode(", ", $tag_ids), count($tag_ids));
    return $where;
}

function tag_assign_id($tag_name) {
    log_assert(is_tag_name($tag_name));
    $id = tag_get_id($tag_name);
    if (is_null($id)) {
        $query = sprintf("INSERT INTO ia_tags (name) VALUES (%s)", db_quote($tag_name));
        db_query($query);
        return db_insert_id();
    }
    return $id;
}

function tag_get_objects($obj, $tag_names, $content = true) {
    log_assert(is_taggable($obj));
    if ($content) {
        $fields = "*";
    }
    else {
        $fields = "id";
    }
    $query = sprintf("SELECT %s FROM ia_%s WHERE %s", $fields, db_escape($obj), 
                     tag_build_where($obj, $tag_names));
    return db_fetch_all($query);
}

function tag_count_objects($obj, $tag_names) {
    log_assert(is_taggable($obj));
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_%s WHERE %s", db_escape($obj), 
                     tag_build_where($obj, $tag_names));
    $result = db_fetch($query);
    return $result['cnt'];
}

function tag_exists($obj, $obj_id, $tag_name) {
    log_assert(is_taggable($obj));
    log_assert(is_tag_name($tag_name));
    $id = tag_get_id($tag_name);
    if (is_null($id)) {
        return false;
    }
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_%s_tags WHERE tag_id = %s AND
                      %s_id = %s", db_escape($obj), db_escape($id), db_escape($obj),
                      db_quote($obj_id));
    $result = db_fetch($query);
    if ($result['cnt'] == 0) {
        return false;
    } 
    return true;
}

function tag_add($obj, $obj_id, $tag_name) {
    log_assert(is_taggable($obj));
    log_assert(is_tag_name($tag_name));
    if (tag_exists($obj, $obj_id, $tag_name)) {
        return;
    }
    $id = tag_assign_id($tag_name);
    $query = sprintf("INSERT INTO ia_%s_tags (tag_id, %s_id) VALUES (%d, %s)", db_escape($obj),
            db_escape($obj), db_escape($id), db_quote($obj_id));
    db_query($query);
}


?>
