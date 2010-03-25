<?php
require_once(IA_ROOT_DIR . "www/macros/macro_stars.php");

include('header.php');
include('format/table.php');

function format_title($row) {
    $title = "<span style=\"float:left;\">".format_link(url_textblock($row["page_name"]), $row["task_title"])."</span>";
    if ($row['open_source'] || $row['open_tests']) {
        $title .= "<span style=\"float:right;\">";
        $title .= format_link(url_task($row['task_id']), format_img(url_static("images/open_small.png"), ""), false);
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

function format_single_author($tag) {
    return format_link(url_task_search(array($tag["id"])), $tag["name"]);
}

function format_authors($row) {
    $authors = $row['authors'];
    return implode(", ", array_map('format_single_author', $authors));
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
echo tag_print($view["tags"]);
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
$column_infos[] = array(
        'title' => 'Număr',
        'css_class' => 'number',
        'rowform' => create_function_cached('$row',
        'return str_pad($row["order"] - 1, 3, \'0\', STR_PAD_LEFT);'));

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
        'key' => 'round_title');
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

if (!count($tasks)) {
    echo "Nicio problemă gasită.";
} else {
    $task_chunks = array_chunk($tasks, $options['display_entries']);
    $chunk = $task_chunks[$options['first_entry'] / $options['display_entries']];
    echo format_table($chunk, $column_infos, $options);
}

include('footer.php');
?>
