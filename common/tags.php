<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/tags.php");

function tag_split($tag_data) {
    $tags = explode(",", $tag_data);
    array_walk($tags, 'trim');
    $result = array();
    foreach ($tags as &$tag) {
        $tag = trim($tag);
        if (strlen($tag) > 0) {
            $result[] = $tag;
        }
    }
    return $result;
}

function tag_validate($data, &$errors, $key = null, $parent_key = null) {
    if (is_null($key)) {
        $tag_key = 'tags';
    } else {
        $tag_key = 'tag_'.$key;
    }
    $tags = getattr($data, $tag_key);
    if (is_null($tags)) {
        return;
    }
    $tags = tag_split($tags);
    foreach ($tags as $tag) {
        if (!is_tag_name($tag)) {
            $errors[$tag_key] = "Cel putin un tag este gresit";
            return;
        }
    }
    if (count($tags) > 0 && !is_null($parent_key)) {
        $parent_tag = getattr($data, 'tag_'.$parent_key, "");
        if (count(tag_split($parent_tag)) != 1) {
            $errors['tag_'.$parent_key] = sprintf("Trebuie specificat exact
                un tag '%s' pentru a specifica taguri '%s'", $parent_key, $key);
        }
    }
}

function tag_build_list($obj, $obj_id, $type, $parent = null) {
    $tag_list = tag_get($obj, $obj_id, $type, $parent);
    $tag_names = array();
    foreach ($tag_list as $tag) {
        $tag_names[] = $tag['name'];
    }
    return implode(", ", $tag_names);
}

// Receives a list of tags which will be arranged in a tree-like structure
// according to their respective parents.
function tag_build_tree($tags) {
    log_assert(is_array($tags), "tag_build_tree expects an array of tags");
    $tags_by_id = array();
    // Group tags by id
    foreach ($tags as &$tag) {
        log_assert(is_tag($tag), "tag_build_tree expects an array of tags");
        log_assert(isset($tag["id"]),
            "tag_build_tree expects an array of tags with id");

        $tags_by_id[$tag["id"]] =& $tag;
    }

    // Add 'sub_tags' attribute to all tags which have children.
    $has_parent = array();
    foreach ($tags as &$tag) {
        if (array_key_exists($tag["parent"], $tags_by_id)) {
            $has_parent[$tag["id"]] = true;

            if (!isset($tags_by_id[$tag["parent"]]["sub_tags"])) {
                $tags_by_id[$tag["parent"]]["sub_tags"] = array();
            }
            $tags_by_id[$tag["parent"]]["sub_tags"][] =& $tag;
        }
    }

    // Return only tree roots (tags which have no parents).
    $roots = array();
    foreach ($tags_by_id as &$tag) {
        if (!isset($has_parent[$tag["id"]])) {
            $roots[] = $tag;
        }
    }
    return $roots;
}

// Receives a list of tags of a certain type and, optionally, with a
// certain parent and updates the tag list for the specified object.
// Returns a list of tag ids.
function tag_update($obj, $obj_id, $type, $tag_data, $parent = 0) {
    tag_clear($obj, $obj_id, $type);
    $tag_data = tag_split($tag_data);
    $tag_ids = array();
    foreach ($tag_data as $tag_name) {
        $tag_id = tag_assign_id(
            array("name" => $tag_name, "type" => $type, "parent" => $parent));
        $tag_ids[] = $tag_id;
        tag_add($obj, $obj_id, $tag_id);
    }
    return $tag_ids;
}

?>
