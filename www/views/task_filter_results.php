<?php
require_once(IA_ROOT_DIR . 'www/macros/macro_stars.php');
require_once(IA_ROOT_DIR . 'www/format/table.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');

include('header.php');

function format_title($row) {
    $title = '<span style=\"float:left;\">' .
             format_link(url_textblock($row["page_name"]),
                         $row["task_title"]) . "</span>";
    if ($row['open_source'] || $row['open_tests']) {
        $title .= "<span style=\"float:right;\">";
        $title .= format_link(url_task($row['task_id']),
                              format_img(url_static("images/open_small.png"),
                                         ""),
                              false);
        $title .= "</span>";
    }
    return $title;
}

function format_score_column($val) {
    if (is_null($val)) {
        return 'N/A';
    } else {
        return round($val);
    }
}

function format_rating_column($val) {
    if (is_null($val)) {
        return 'N/A';
    } else {
        $stars_args = array(
            'rating' => $val,
            'scale' => 5,
            'type' => 'normal'
        );
        return macro_stars($stars_args);
    }
}

function format_authors($row) {
    $authors = $row['authors'];

   return implode(", ", format_task_author_tags($authors));
}

function task_row_style($row) {
    $score = getattr($row, 'score');
    if (is_null($score)) {
        return '';
    }

    log_assert(is_numeric($score));
    $score = (int)$score;

    if (100 == $score) {
        return 'solved';
    }
    else {
        return 'tried';
    }
}

function tag_print($tags) {
    if (count($tags) == 0) {
        return "";
    }

    $tag_types = array(
        "author" => "Autor",
        "contest" => "Concurs",
        "year" => "Editie",
        "round" => "Runda",
        "age_group" => "Grupa de varsta",
        "method" => "Categorie",
        "algorithm" => "Algoritm",
        "tag" => "Tag"
    );
    $output = '<ul class="tag_filters">';
    foreach ($tags as $tag) {
        $output .= sprintf("<li>%s%s</li>",
            format_link(url_task_search(array($tag["id"])), sprintf("%s: %s",
                $tag_types[$tag["type"]], $tag["name"])),
            tag_print(getattr($tag, "sub_tags", array()))
        );
    }
    $output .= "</ul>";
    return $output;
}

echo "<h1>Rezultatele filtrării</h1>";

$tag_ids = $selected_tags;
$tasks = $view['tasks'];
$options = pager_init_options();
$options['total_entries'] = count($tasks);
$options['row_style'] = 'task_row_style';
$options['css_class'] = 'tasks fill-screen filter_results';
$options['show_count'] = true;
$options['show_display_entries'] = false;

if (identity_is_anonymous()) {
    $user_id = null;
} else {
    $user_id = identity_get_user_id();
}

$column_infos = array();

$current_row = getattr($options, 'first_entry', 0);
$column_infos[] = array(
        'title' => 'Număr',
        'css_class' => 'number',
        'rowform' => function($row) use (&$current_row) {
            return str_pad(++$current_row, 3, '0', STR_PAD_LEFT);
        });

$column_infos[] = array(
        'title' => 'Titlul problemei',
        'css_class' => 'task',
        'rowform' => 'format_title');
$column_infos[] = array(
        'title' => 'Autor(i)',
        'css_class' => 'author',
        'rowform' => 'format_authors');
$column_infos[] = array(
        'title' => 'Sursă',
        'css_class' => 'source',
        'key' => 'source');
$column_infos[] = array(
        'title' => 'Dificultate',
        'css_class' => 'rating',
        'key' => 'rating',
        'valform' => 'format_rating_column');
if (!is_null($user_id)) {
    $column_infos[] = array (
            'title' => 'Scorul tău',
            'css_class' => 'score',
            'key' => 'score',
            'valform' => 'format_score_column');
}

echo
'<div id="task-filter-help">
    <div> </div> Tag neselectat
    <div class="selected-filter"> </div> Tag selectat
    <div class="sub-selected-filters"> </div>
        Categorie din care e selectat cel putin un tag
</div>';

echo '<br><div id="task-filter-menu">';

// First all algorithmic tags
foreach ($view['tags'] as &$tag) {
    $tag['classes'] = array();
    $has_subtags_selected = false;

    foreach ($tag['sub_tags'] as &$subtag) {
        $subtag['classes'] = array();
        if (in_array($subtag['id'], $tag_ids)) {
            $has_subtags_selected = true;
            $subtag['classes'][] = 'selected-filter';
            $subtag['nocount'] = true;
        }
    }

    if (in_array($tag['id'], $tag_ids)) {
        $tag['classes'][] = 'selected-filter';
        // TODO: count removable tags
        $tag['nocount'] = true;
    }

    if ($has_subtags_selected) {
        $tag['classes'][] = 'sub-selected-filters';
        // TODO: count removable tags
        $tag['nocount'] = true;
    }
}

echo format_task_tag_menu($view['tags'], $tag_ids);

// Damn that was hard, guess what's next, authors :-(
// Let's split them by first letter
$groups = array();
$groups[] = array('name' => 'A-E', 'id' => '', 'group' => 'abcde',
                  'sub_tags' => array(), 'classes' => array());
$groups[] = array('name' => 'F-J', 'id' => '', 'group' => 'fghij',
                  'sub_tags' => array(), 'classes' => array());
$groups[] = array('name' => 'K-O', 'id' => '', 'group' => 'klmno',
                  'sub_tags' => array(), 'classes' => array());
$groups[] = array('name' => 'P-T', 'id' => '', 'group' => 'pqrst',
                  'sub_tags' => array(), 'classes' => array());
$groups[] = array('name' => 'U-W', 'id' => '', 'group' => 'uvwxyz',
                  'sub_tags' => array(), 'classes' => array());

echo 'Autori';
// First we have to sort them
usort($authors, function($a, $b) {
                    return strcmp(strtolower($a['name']),
                                  strtolower($b['name']));
                });
foreach ($authors as &$tag) {
    // Let's get the group, just a simple "for" right?
    foreach ($groups as &$group) {
        if (strchr($group['group'], strtolower($tag['name'][0]))) {
            $current_group = &$group;
            break;
        }
    }

    $tag['classes'] = array();
    if (in_array($tag['id'], $tag_ids)) {
        $tag['classes'][] = 'selected-filter';
        $current_group['classes'][] = 'sub-selected-filters';
    }

    $current_group['sub_tags'][] = $tag;
}

// Eliminating empty groups
$real_groups = array();
foreach ($groups as &$group) {
    if (count($group['sub_tags']) > 0) {
        $real_groups[] = $group;
    }
}

echo format_task_tag_menu($real_groups, $tag_ids);

echo '</div><div id="task-filter-table">';
if (!count($tasks)) {
    echo "Nicio problemă gasită.";
} else {
    $chunk = array();
    $first_entry = getattr($options, 'first_entry', 0);
    $display_entries = getattr($options, 'display_entries', 25);
    $total_entries = count($tasks);

    for ($i = $first_entry; $i < $first_entry + $display_entries
                                && $i < $total_entries; ++$i) {
        $chunk[] = $tasks[$i];
    }
    echo format_table($chunk, $column_infos, $options);
}
echo '</div>';
include('footer.php');
?>
