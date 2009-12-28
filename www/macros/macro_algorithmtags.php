<?php
require_once(IA_ROOT_DIR.'common/tags.php');
require_once(IA_ROOT_DIR.'common/db/tags.php');
require_once(IA_ROOT_DIR.'common/db/db.php');
require_once(IA_ROOT_DIR.'common/db/task.php');

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

    $tags_tree = tag_build_tree(array_merge($tags, $sub_tags));

    $task = task_get($task_id);
    if (!identity_can('task-view-tags', $task)) {
        return "";
    }

    if (count($tags_tree) == 0) {
        return "";
    }

    $cnt_category = count($tags_tree);
    $category_word = "categorii";
    if ($cnt_category == 1) {
        $category_word = "categorie";
    }
    $html_code = "<div id=\"task_tags\">";
    $html_code .= "<h3> Indicii de rezolvare</h3>";
    $html_code .= '<a id="show_tags" href="javascript:show_tags()">
                Arată '.count($tags_tree).' '.$category_word.'</a>';

    $html_code .= '<ul id="task_tags">';
    foreach ($tags_tree as $tag) {
        $tag_id = $tag['id'];
        $tag_name = $tag['name'];
        $cnt_subtags = count($tag['sub_tags']);
        if ($cnt_subtags > 1) {
            $tag_word = 'taguri';
        } else {
            $tag_word = 'tag';
        }

        $subtags_html = Array();
        foreach ($tag['sub_tags'] as $subtag) {
            $tag_link = format_link(url_task_search(array($subtag['id'])), $subtag['name'], true,
                            array('class' => "sub_tag_search_anchor"));
            $subtags_html[] = '<div class="sub_tag_name">'.$tag_link.'</div>';
        }

        $color_scheme = $tag_id % 6;
        $tag_link = format_link(url_task_search(array($tag_id)), $tag_name, true, array('class' => 'tag_search_anchor'));
        $html_code .= '
        <li style="display: none;" class="tags_list_item">
            <span class="tag_name color_scheme_'.$color_scheme.'">'.$tag_link.'</span>
            <a href="javascript:show_tag_list('.html_escape($tag_id).')"
                    id="tag_anchor_'.html_escape($tag_id).'"
                    class="show_tag_anchor"
            >
                ... '.$cnt_subtags.' '.$tag_word.'
            </a>
            <div style="display: none;"
                id="tag_list_'.html_escape($tag_id).'"
            >&nbsp;
                '.implode('&nbsp;', $subtags_html).'
            </div>
        </li>';
    }
    $html_code .= "</ul></div>";
    return $html_code;
}
?>
