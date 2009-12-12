<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/tags.php");

function tag_split($tag_data) {
    $tags = explode(",", trim($tag_data));
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

function tag_build_list($obj, $obj_id, $type) {
    $tag_list = tag_get($obj, $obj_id, $type);
    $tag_names = array();
    foreach ($tag_list as $tag) {
        $tag_names[] = $tag['tag_name'];
    }
    return implode(", ", $tag_names);
}

function tag_update($obj, $obj_id, $type, $tag_data) {
    tag_clear($obj, $obj_id, $type);
    $tag_data = tag_split($tag_data);
    foreach ($tag_data as $tag_name) {
        $tag_id = tag_assign_id(array("name" => $tag_name, "type" => $type));
        tag_add($obj, $obj_id, $tag_id);
    }
}

?>
