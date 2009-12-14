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

function tag_validate($data, &$errors) {
    $tags = getattr($data, 'tags');
    if (is_null($tags)) {
        return;
    }
    $tags = tag_split($tags);
    foreach ($tags as $tag) {
        if (!is_tag_name($tag)) {
            $errors['tags'] = "Cel putin un tag este gresit";
            return;
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
