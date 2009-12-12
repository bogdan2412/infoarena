<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/common.php");

// Get list of all tag names, filtered by type
function tag_get_all($types = null) {
    $query = "SELECT name, type FROM ia_tags";
    if (!is_null($types)) {
        log_assert(is_array($types), "types should be an array");
        foreach ($types as $type) {
            log_assert(is_tag_type($type));
        }

        $query .= sprintf(" WHERE type IN (%s)",
            implode(',', array_map('db_quote', $types))
        );
    }
    return db_fetch_all($query);
}

// Get list of all tags for a certain object, filtered by type
function tag_get($obj, $obj_id, $type = null) {
    log_assert(is_taggable($obj));
    log_assert(is_null($type) || is_tag_type($type));
    if (is_null($type)) {
        $where_type = "";
    } else {
        $where_type = sprintf(" AND tags.type = %s", db_quote($type));
    }
    $query = sprintf(
        "SELECT %s_id, tag_id, tags.name AS tag_name, tags.type AS tag_type
        FROM ia_%s_tags AS obj_tags
        LEFT JOIN ia_tags AS tags ON obj_tags.tag_id = tags.id
        WHERE %s_id = %s%s",
        db_escape($obj), db_escape($obj), db_escape($obj),
        db_quote($obj_id), $where_type
    );
    return db_fetch_all($query);
}

// Get tag id for a certain tag
function tag_get_id($tag) {
    log_assert(is_tag($tag));
    $query = sprintf(
        "SELECT id FROM ia_tags WHERE name = %s AND type = %s",
        db_quote($tag["name"]), db_quote($tag["type"])
    );
    $result = db_fetch($query);
    return $result['id'];
}

// Get list of tag ids for a list of tags
function tag_get_ids($tags) {
    $tag_wheres = array();
    foreach ($tags as $tag) {
        log_assert(is_tag($tag));
        $tag_wheres[] = sprintf("(name = %s AND type = %s)",
            db_quote($tag["name"]), db_quote($tag["type"]));
    }
    $query = sprintf("SELECT id FROM ia_tags WHERE %s",
        implode(" OR ", $tag_wheres));
    return db_fetch_all($query);
}

// Assign numeric id to a given tag name
function tag_assign_id($tag) {
    log_assert(is_tag($tag));
    $id = tag_get_id($tag);
    if (is_null($id)) {
        $query = sprintf("INSERT INTO ia_tags (name, type) VALUES (%s, %s)",
            db_quote($tag['name']), db_quote($tag['type']));
        db_query($query);
        return db_insert_id();
    }
    return $id;
}

// Build ugly where clause to be used in subqueries
function tag_build_where($obj, $tag_ids, $parent_table = null) {
    log_assert(is_taggable($obj));
    log_assert(is_array($tag_ids));
    if (is_null($parent_table)) {
        $parent_table = "ia_".db_escape($obj);
    }
    if ($obj == 'textblock') {
        $field = 'name';
    } else {
        $field = 'id';
    }
    $where = sprintf("(SELECT COUNT(*) FROM ia_%s_tags WHERE %s_id = %s.%s".
                     " AND tag_id IN (%s)) = %d",
                     db_escape($obj), db_escape($obj),
                     db_escape($parent_table), db_escape($field),
                     implode(", ", $tag_ids), count($tag_ids));
    return $where;
}

// Get all objects containting all tags from a list of tag ids
function tag_get_objects($obj, $tag_ids, $content = true) {
    log_assert(is_taggable($obj));
    log_assert(is_array($tag_ids));
    if ($content) {
        $fields = "*";
    } elseif ($obj == 'textblock') {
        $fields = "name";
    } else {
        $fields = "id";
    }
    $query = sprintf("SELECT %s FROM ia_%s WHERE %s", $fields, db_escape($obj),
                     tag_build_where($obj, $tag_ids));
    return db_fetch_all($query);
}

// Count the number of objects containing all tags from a list of tag ids
function tag_count_objects($obj, $tag_ids) {
    log_assert(is_taggable($obj));
    log_assert(is_array($tag_ids));
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_%s WHERE %s", db_escape($obj),
                     tag_build_where($obj, $tag_ids));
    $result = db_fetch($query);
    return $result['cnt'];
}

// Clear all tags, filtered by type
function tag_clear($obj, $obj_id, $type = null) {
    log_assert(is_taggable($obj));
    log_assert(is_null($type) || is_tag_type($type));
    $where = sprintf("%s_id = %s", db_escape($obj), db_quote($obj_id));
    $query = sprintf("DELETE FROM ia_%s_tags WHERE %s AND tag_id IN (%s)",
        db_escape($obj), $where, "%s"
    );
    if (is_null($type)) {
        $join = "";
    } else {
        $join = "LEFT JOIN ia_tags AS tags ON obj_tags.tag_id = tags.id";
        $where .= sprintf(" AND tags.type = %s", db_quote($type));
    }
    $subquery = sprintf("SELECT tag_id FROM ia_%s_tags AS obj_tags %s WHERE %s",
        db_escape($obj), $join, $where);
    $tag_ids = array();
    foreach (db_fetch_all($subquery) as $tag) {
        $tag_ids[] = $tag["tag_id"];
    }
    if ($tag_ids) {
        $query = sprintf($query, implode(",", $tag_ids));
        db_query($query);
    }
}

// Check if a certain object has a certain tag
function tag_exists($obj, $obj_id, $tag_id) {
    log_assert(is_taggable($obj));
    log_assert(is_tag_id($tag_id));
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_%s_tags WHERE tag_id = %s AND
                      %s_id = %s", db_escape($obj), db_escape($tag_id), db_escape($obj),
                      db_quote($obj_id));
    $result = db_fetch($query);
    if ($result['cnt'] == 0) {
        return false;
    }
    return true;
}

// Remove a tag
function tag_remove($obj, $obj_id, $tag_id) {
    log_assert(is_taggable($obj));
    log_assert(is_tag_id($tag_id));
    $query = sprintf("DELETE FROM ia_%s_tags WHERE tag_id = %s AND %s_id = %s",
                     db_escape($obj), db_escape($tag_id), db_escape($obj), db_quote($obj_id));
    db_query($query);
}

// Add a tag
function tag_add($obj, $obj_id, $tag_id) {
    log_assert(is_taggable($obj));
    log_assert(is_tag_id($tag_id));
    if (tag_exists($obj, $obj_id, $tag_id)) {
        return;
    }
    $query = sprintf("INSERT INTO ia_%s_tags (tag_id, %s_id) VALUES (%d, %s)", db_escape($obj),
            db_escape($obj), db_escape($tag_id), db_quote($obj_id));
    db_query($query);
}
?>
