<?php
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

echo "<h1>Rezultatele Filtrarii</h1>";
$tasks = $view['tasks'];
$options = pager_init_options();
$options['total_entries'] = count($tasks);
$options['row_style'] = 'task_row_style';
$options['css_class'] = 'tasks filter_results';
$options['show_count'] = true;
$options['show_display_entries'] = false;

if (identity_is_anonymous()) {
    $user_id = null;
} else {
    $user_id = identity_get_user_id();
}

$column_infos = array();
$column_infos[] = array(
        'title' => 'Numar',
        'css_class' => 'number',
        'rowform' => create_function_cached('$row',
        'return str_pad($row["order"] - 1, 3, \'0\', STR_PAD_LEFT);'));

$column_infos[] = array(
        'title' => 'Titlul problemei',
        'css_class' => 'task',
        'rowform' => 'format_title');
$column_infos[] = array(
        'title' => 'Autor(i)',
        'rowform' => 'format_authors');
$column_infos[] = array(
        'title' => 'Sursa',
        'css_class' => 'source',
        'key' => 'round_title',);
if (!is_null($user_id)) {
    $column_infos[] = array (
            'title' => 'Scorul tau',
            'css_class' => 'number score',
            'key' => 'score',
            'valform' => 'format_score_column');
}

$task_chunks = array_chunk($tasks, $options['display_entries']);
$chunk = $task_chunks[$options['first_entry'] / $options['display_entries']];
echo format_table($chunk, $column_infos, $options);
include('footer.php');
?>
