<?php

require_once(IA_ROOT . "www/format/table.php");
require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "common/db/round.php");

function format_score_column($val) {
    if (is_null($val)) {
        return 'N/A';
    }
    else {
        return $val;
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
    $round = round_get($round_id);
    if (!$round) {
        return macro_error('Invalid round identifier');
    }

    if (is_null(getattr($args, 'score'))) {
        $scores = false;
    } else {
        $scores = true;
    }

    if (identity_anonymous() || $scores == false) {
        $user_id = null;
    } else {
        $user_id = identity_get_user_id();
    }

    // get round tasks
    $tasks = round_get_task_info($round_id,
                                 $options['first_entry'],
                                 $options['display_entries'],
                                 $user_id, ($scores ? 'score' : null));
    $options['total_entries'] = round_get_task_count($round_id);
    $options['row_style'] = 'task_row_style';
    $options['css_class'] = 'tasks';

    $column_infos = array(
            array(
                'title' => 'Titlul problemei',
                'css_class' => 'task',
                'rowform' => create_function('$row',
                        'return "<a href=\"".url($row["page_name"])."\">".$row["title"]."</a>";'),
            ),
    );
    if ($user_id !== null) {
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
