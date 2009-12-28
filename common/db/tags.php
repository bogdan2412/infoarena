<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/common.php");
require_once(IA_ROOT_DIR."common/cache.php");

// Get list of all tag names, filtered by type and parent
function tag_get_all($types = null, $parent = null) {
    $query = "SELECT id, name, type, parent FROM ia_tags";
    $where = array();
    if (!is_null($types)) {
        log_assert(is_array($types), "types should be an array");
        foreach ($types as $type) {
            log_assert(is_tag_type($type));
        }

        $where[] = sprintf("(type IN (%s))",
            implode(',', array_map('db_quote', $types))
        );
    }
    if (!is_null($parent)) {
        log_assert(is_tag_id($parent));
        $where[] = sprintf("(parent = %s)", db_quote($parent));
    }
    if (count($where)) {
        $query .= sprintf(" WHERE %s", implode(" AND ", $where));
    }
    $query .= " ORDER BY name";
    return db_fetch_all($query);
}

// Get list of all tags for a certain object, filtered by type
function tag_get($obj, $obj_id, $type = null, $parent = null) {
    log_assert(is_taggable($obj));
    log_assert(is_null($type) || is_tag_type($type));
    if (is_null($type)) {
        $where_type = "";
    } else {
        $where_type = sprintf(" AND tags.type = %s", db_quote($type));
    }
    if (is_null($parent)) {
        $where_parent = "";
    } else {
        $where_parent = sprintf(" AND tags.parent = %s", db_quote($parent));
    }
    $query = sprintf(
        "SELECT %s_id, tag_id AS id,
        tags.name AS name, tags.type AS type, tags.parent AS parent
        FROM ia_%s_tags AS obj_tags
        LEFT JOIN ia_tags AS tags ON obj_tags.tag_id = tags.id
        WHERE %s_id = %s%s%s
        ORDER BY name",
        db_escape($obj), db_escape($obj), db_escape($obj),
        db_quote($obj_id), $where_type, $where_parent
    );
    return db_fetch_all($query);
}

// Receives an array of tag_id's
// Return an array with id's of their parents
// Each parent id appears only once
function tag_get_parents($tag_ids) {
    $query = sprintf("SELECT DISTINCT(`parent`)
            FROM ia_tags
            WHERE `id` IN (%s) AND `parent` != 0",
            implode(",", array_map('db_quote', $tag_ids))
        );
    $result = db_fetch_all($query);
    $ret = array();
    foreach ($result as $row) {
        $ret[] = $row['parent'];
    }
    return $ret;
}

// Get tag id for a certain tag
function tag_get_id($tag) {
    log_assert(is_tag($tag));
    $query = sprintf(
        "SELECT id FROM ia_tags WHERE name = %s AND type = %s AND parent = %s",
        db_quote($tag["name"]), db_quote($tag["type"]), db_quote($tag["parent"])
    );
    $result = db_fetch($query);
    return $result['id'];
}

// Get list of tag ids for a list of tags
function tag_get_ids($tags) {
    $tag_wheres = array();
    foreach ($tags as $tag) {
        log_assert(is_tag($tag));
        $tag_wheres[] = sprintf(
            "(name = %s AND type = %s AND parent = %s)",
            db_quote($tag["name"]), db_quote($tag["type"]),
            db_quote($tag["parent"])
        );
    }
    $query = sprintf("SELECT id FROM ia_tags WHERE %s",
        implode(" OR ", $tag_wheres));
    return db_fetch_all($query);
}

// Get a list of tags from a list of tag ids
function tag_get_by_ids($tag_ids) {
    $query = sprintf(
            "SELECT `id`, `name`, `type`, `parent`
            FROM ia_tags
            WHERE `id` IN (%s)", implode(', ', array_map('db_quote', $tag_ids))
        );
    return db_fetch_all($query);
}

// Assign numeric id to a given tag name
function tag_assign_id($tag) {
    log_assert(is_tag($tag));
    $id = tag_get_id($tag);
    if (is_null($id)) {
        $query = sprintf(
            "INSERT INTO ia_tags (name, type, parent) VALUES (%s, %s, %s)",
            db_quote($tag['name']), db_quote($tag['type']),
            db_quote($tag['parent'])
        );
        db_query($query);
        return db_insert_id();
    }
    return $id;
}

// Updates name, type or parent for a tag specified by it's id
function tag_update_by_id($tag_id, $tag) {
    $query = sprintf(
        "UPDATE ia_tags SET name = %s, type = %s, parent = %s WHERE id = %s",
        db_quote($tag["name"]), db_quote($tag["type"]),
        db_quote($tag["parent"]), db_quote($tag_id));
    db_query($query);
    return db_affected_rows() == 1;
}

// Delete a tag identified by it's id
function tag_delete_by_id($tag_id) {
    db_query(sprintf(
        "DELETE FROM ia_tags WHERE parent = %s", db_quote($tag_id)
    ));
    db_query(sprintf("DELETE FROM ia_tags WHERE id = %s", db_quote($tag_id)));
    return db_affected_rows() == 1;
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
function tag_count_objects($obj, $tag_ids, $no_cache=false) {
    log_assert(is_taggable($obj));
    log_assert(is_array($tag_ids));
    // Cache object count for single tags.
    if (!$no_cache && count($tag_ids) == 1) {
        $result = mem_cache_get("$obj-count-with-tag:".$tag_ids[0]);
        if ($result !== false) {
            return $result;
        }
    }
    $query = sprintf("SELECT COUNT(*) as `cnt` FROM ia_%s WHERE %s", db_escape($obj),
                     tag_build_where($obj, $tag_ids));
    $result = db_fetch($query);
    $result = $result['cnt'];
    if (count($tag_ids) == 1) {
        mem_cache_set("$obj-count-with-tag:".$tag_ids[0], $result,
            IA_MEM_CACHE_TAGS_EXPIRATION);
    }
    return $result;
}

// Clear all tags, filtered by type
function tag_clear($obj, $obj_id, $type = null) {
    log_assert(is_taggable($obj));
    log_assert(is_null($type) || is_tag_type($type));
    $where = sprintf("%s_id = %s", db_escape($obj), db_quote($obj_id));
    $query = sprintf("DELETE FROM ia_%s_tags WHERE %s AND tag_id IN (%s)",
        db_escape($obj), $where, "%s"
    );

    $join = "";
    if (!is_null($type)) {
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
