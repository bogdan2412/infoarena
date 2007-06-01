<?php

require_once(IA_ROOT_DIR."common/db/db.php");

function validate_tag_data($data, &$errors) {
    $tag_data = getattr($data, 'tags');
    if (is_null($tag_data)) {
        return;
    }
    $tags = explode(",", $tag_data);
    foreach ($tags as &$tag) {
        $tag = trim($tag);
        if (!is_tag_name($tag)) {
            $errors['tags'] = "Tag incorect";
            return;
        }
    }
}

function build_tag_list($obj, $obj_id) {
}

?>
