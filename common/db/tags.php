<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/common.php");

// get list of all tag names
function tag_get_all_names() {
    $query = sprintf("SELECT name FROM ia_tags");
    $result = db_fetch_all($query);
    $names = array();
    foreach ($result as $row) {
        $names[] = $row['name'];
    }
    return $names;
}

function tag_get_names($obj, $obj_id) {
    log_assert(is_taggable($obj));
    $query = sprintf("SELECT %s_id, tag_id, tags.name FROM ia_%s_tags AS obj_tags
                      WHERE tag_id = %s
                      LEFT JOIN ia_tags AS tags ON obj_tags.tag_id = tags.id", 
                      db_escape($obj), db_escape($obj), db_quote($obj_id));

    
}

// get tag id for a certain tag name
function tag_get_id($tag_name) {
    log_assert(is_tag_name($tag_name));
    $query = sprintf("SELECT id FROM ia_tags WHERE name = %s",
                     db_quote($tag_name));
    $result = db_fetch($query);
    return $result['id'];
}

// get list of tag ids for a list of tag names
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

// build ugly where clause to be used in subqueries
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

// assign numeric id to a given tag name 
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

// get all objects containting all tags from a list of tag names
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

// count the number of objects containing all tags from a list of tag names
function tag_count_objects($obj, $tag_names) {
    log_assert(is_taggable($obj));
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_%s WHERE %s", db_escape($obj), 
                     tag_build_where($obj, $tag_names));
    $result = db_fetch($query);
    return $result['cnt'];
}

// check if a certain object has a certain tag 
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

// remove a tag
function tag_remove($obj, $obj_id, $tag_name) {
    log_assert(is_taggable($obj));
    log_assert(is_tag_name($tag_name));
    $id = tag_get_id($tag_name);
    if (is_null($id)) {
        return false;
    }
    $query = sprintf("DELETE * FROM ia_%s_tags WHERE tag_id = %s AND %s_id = %s",
                     db_escape($obj), db_escape($id), db_escape($obj), db_quote($obj_id));
    db_query($query);
    return true;
}

// add a tag
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
