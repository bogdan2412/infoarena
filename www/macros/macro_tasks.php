<?php

require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "common/db/round.php");
require_once(IA_ROOT_DIR . "common/round.php");

function format_score_column($val) {
    if (is_null($val)) {
        return 'N/A';
    } else {
        return round($val);
    }
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

// Lists all tasks attached to a given round
// Takes into consideration user permissions.
//
// Arguments;
//      round_id (required)     Round identifier
//
// Examples:
//      Tasks(round_id="archive")
//
// FIXME: print current user score, difficulty rating, etc.
// FIXME: security. Only reveals task names, but still...
function macro_tasks($args) {
    $options = pager_init_options($args);

    $round_id = getattr($args, 'round_id');
    if (!$round_id) {
        return macro_error('Expecting argument `round_id`');
    }

    // fetch round info
    if (!is_round_id($round_id)) {
        return macro_error('Invalid round identifier');
    }
    $round = round_get($round_id);
    if (is_null($round)) {
        return macro_error('Round not found');
    }
    log_assert_valid(round_validate($round));


    // Check if user can see round tasks
    if (!identity_can('round-view-tasks', $round)) {
        return macro_permission_error();
    }


    $scores = !is_null(getattr($args, 'score'));
    if (identity_is_anonymous() || $scores == false) {
        $user_id = null;
    } else {
        $user_id = identity_get_user_id();
    }

    $show_numbers = getattr($args, 'show_numbers', false);
    $show_authors = getattr($args, 'show_authors', true);

    // get round tasks
    $tasks = round_get_tasks($round_id,
             $options['first_entry'],
             $options['display_entries'],
             $user_id, ($scores ? 'score' : null));
    $options['total_entries'] = round_get_task_count($round_id);
    $options['row_style'] = 'task_row_style';
    $options['css_class'] = 'tasks';

    $column_infos = array();
    if ($show_numbers) {
        $column_infos[] = array(
                'title' => 'Numar',
                'css_class' => 'number',
                'rowform' => create_function_cached('$row',
                        'return str_pad($row["order"] - 1, 3, \'0\', STR_PAD_LEFT);'),
        );
    }
    $column_infos[] = array(
            'title' => 'Titlul problemei',
            'css_class' => 'task',
            'rowform' => create_function_cached('$row',
                    'return format_link(url_textblock($row["page_name"]), $row["title"]);'),
    );
    if ($show_authors) {
        $column_infos[] = array(
                'title' => 'Autor',
                'css_class' => 'author',
                'key' => 'author',
        );
    }
    if (!is_null($user_id)) {
        $column_infos[] = array (
                'title' => 'Scorul tau',
                'css_class' => 'number score',
                'key' => 'score',
                'valform' => 'format_score_column',
        );
    }

    return format_table($tasks, $column_infos, $options);
}

?>
