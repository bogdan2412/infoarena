<?php
require_once(IA_ROOT_DIR.'common/tags.php');
require_once(IA_ROOT_DIR.'common/db/tags.php');
require_once(IA_ROOT_DIR.'common/db/db.php');

// ==AlgorithmTags(task_id="task_id")==
// Shows the algorithm tags attached to a tasked
// Structured by categories
function macro_algorithmtags($args) {
    $task_id = getattr($args, 'task_id');
    if (!is_task_id($task_id)) {
        return macro_error("Invalid `task_id` parameter");
    }

    $tags = tag_get('task', $task_id, 'method');
    $sub_tags = tag_get('task', $task_id, 'algorithm');

    $tags_tree = build_tags_tree($tags, $sub_tags);

    $task = task_get($task_id);
    if (!identity_can('task-view-tags', $task)) {
        return "";
    }

    if (count($tags_tree) == 0) {
        return "";
    }

    $html_code = "<div id=\"task_tags\">";
    $html_code .= "<h3> Indicaţii de rezolvare</h3>";
    $html_code .= '<a id="show_tags" href="javascript:show_tags()">
                Arată '.count($tags_tree).' categorii</a>';

    $html_code .= '<ul id="task_tags">';
    foreach ($tags_tree as $tag) {
        $tag_id = $tag['tag_id'];
        $tag_name = $tag['tag_name'];
        $cnt_subtags = count($tag['sub_tags']);
        if ($cnt_subtags > 1) {
            $tag_word = 'taguri';
        } else {
            $tag_word = 'tag';
        }

        $subtags_html = Array();
        foreach ($tag['sub_tags'] as $subtag) {
            $subtags_html[] = '<div class="sub_tag_name">'.$subtag['tag_name'].'</div>';
        }

        $color_scheme = $tag_id % 6;
        $html_code .= '
        <li style="display: none;" class="tags_list_item">
            <span class="tag_name color_scheme_'.$color_scheme.'">'.html_escape($tag_name).'</span>
            <a href="javascript:show_tag_list('.html_escape($tag_id).')"
                    id="tag_anchor_'.html_escape($tag_id).'"
                    class="show_tag_anchor"
            >
                ... '.$cnt_subtags.' '.$tag_word.'
            </a>
            <div style="display: none;"
                id="tag_list_'.html_escape($tag_id).'"
            >:
                '.implode(' ', $subtags_html).'
            </div>
        </li>';
    }
    $html_code .= "</ul></div>";
    return $html_code;
}
?>
