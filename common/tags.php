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
        $tag_names[] = $tag['tag_name'];
    }
    return implode(", ", $tag_names);
}

// Receives a list of parent tags and children tags
// For each parent_tag adds an array 'sub_tags' containing
// an array with all his children tags
function build_tags_tree($parent_tags, $sub_tags) {
    log_assert(is_array($parent_tags), "Parent tags is not an array");
    log_assert(is_array($sub_tags), "Children tags is not an array");

    $parent_tags_key = Array();
    foreach ($parent_tags as $key => $tag) {
        $parent_tags[$key]['sub_tags'] = Array();
        $parent_tags_key[$tag['tag_id']] = $key;
    }

    foreach ($sub_tags as $tag) {
        log_assert(isset($parent_tags_key[$tag['tag_parent']]), "Child tag doesn't have a parent");
        $parent_tag_key = $parent_tags_key[$tag['tag_parent']];
        $parent_tags[$parent_tag_key]['sub_tags'][] = $tag;
    }

    return $parent_tags;
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
