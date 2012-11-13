<?php
require_once(IA_ROOT_DIR . "common/tags.php");
require_once(IA_ROOT_DIR . "common/string.php");

// Displays an interface in which admins can define algorithm tag categories
function controller_task_tags() {
    identity_require("task-tag");

    $categories = tag_build_tree(
                    tag_get_with_counts(array("method", "algorithm"),
                                      array(), true));

    $authors = tag_get_with_counts(array("author"), array(), true);

    // Create view.
    $view = array();
    $view['title'] = "Tag-uri probleme";
    $view['categories'] = $categories;
    $view['authors'] = $authors;
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

    // Do not delete tags if they have been added to tasks.
    $task_count = tag_count_objects("task", array($tag_id), true);
    if ($task_count != 0) {
        flash_error("Nu poti sterge un tag care a fost asociat deja unei probleme.");
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
    // Check that the new tag doesn't already exist.
    if (tag_get_id($tag)) {
        flash_error("Tagul deja exista.");
        redirect(url_task_tags());
    }
    tag_update_by_id($tag_id, $tag);
    // Clear author cache for all tasks tagged with the tag.
    if ($tag["type"] == "author") {
        $task_ids = tag_get_objects("task", array($tag_id), false);
        foreach ($task_ids as $task_id) {
            mem_cache_delete("task-authors-by-id:".$task_id["id"]);
        }
    }

    flash("Tag-ul a fost redenumit.");
    redirect(url_task_tags());
}
