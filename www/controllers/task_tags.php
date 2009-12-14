<?php
require_once(IA_ROOT_DIR . "common/db/tags.php");
require_once(IA_ROOT_DIR . "common/string.php");

// Displays an interface in which admins can define algorithm tag categories
function controller_task_tags() {
    identity_require("task-tag");

    $categories = tag_get_all(array("method"));
    foreach ($categories as &$category) {
        // Get all sub tags for current category
        $category["sub_tags"] = tag_get_all(array("algorithm"),
            $category["id"]);
    }

    // Create view.
    $view = array();
    $view['title'] = "Tag-uri probleme";
    $view['categories'] = $categories;
    execute_view_die("views/task_tags.php", $view);
}

// Create a "method" or "algorithm" tag
function controller_task_tags_add() {
    identity_require("task-tag");
    if (!request_is_post()) {
        redirect(url_task_tags());
    }

    $tag = array(
        "name" => request("name"),
        "type" => request("type"),
        "parent" => request("parent", 0)
    );
    if (!is_tag($tag)) {
        flash_error("Nume de tag invalid.");
        redirect(url_task_tags());
    }
    $tag_id = tag_get_id($tag);
    if (!is_null($tag_id)) {
        flash_error("Tagul exista deja.");
        redirect(url_task_tags());
    }

    tag_assign_id($tag);
    redirect(url_task_tags());
}

// Delete a "method" or "algorithm" tag
function controller_task_tags_delete() {
    identity_require("task-tag");
    if (!request_is_post()) {
        redirect(url_task_tags());
    }

    $tag = array(
        "name" => request("name"),
        "type" => request("type"),
        "parent" => request("parent", 0)
    );
    if (!is_tag($tag)) {
        flash_error("Nume de tag invalid.");
        redirect(url_task_tags());
    }
    $tag_id = tag_get_id($tag);
    if (is_null($tag_id)) {
        flash_error("Tag inexistent.");
        redirect(url_task_tags());
    }

    tag_delete_by_id($tag_id);
    flash("Tag-ul a fost sters.");
    redirect(url_task_tags());
}

// Rename a "method" or "algorithm" tag
function controller_task_tags_rename() {
    identity_require("task-tag");
    if (!request_is_post()) {
        redirect(url_task_tags());
    }

    $tag = array(
        "name" => request("old_name"),
        "type" => request("type"),
        "parent" => request("parent", 0)
    );
    $new_name = request("name", "");
    if (!is_tag($tag) || !is_tag_name($new_name)) {
        flash_error("Nume de tag invalid.");
        redirect(url_task_tags());
    }
    $tag_id = tag_get_id($tag);
    if (is_null($tag_id)) {
        flash_error("Tag inexistent.");
        redirect(url_task_tags());
    }

    $tag["name"] = $new_name;
    tag_update_by_id($tag_id, $tag);
    flash("Tag-ul a fost redenumit.");
    redirect(url_task_tags());
}
?>
