<?php

require_once(IA_ROOT . "www/format/table.php");
require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "common/db/round.php");

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
//    log_print("UID: $user_id");

    // get round tasks
    $tasks = round_get_task_info($round_id,
            $options['first_entry'],
            $options['display_entries'],
            $user_id, ($scores ? 'score' : null));
    $options['total_entries'] = round_get_task_count($round_id);

    $column_infos = array(
            array(
                'title' => 'Titlul problemei',
                'rowform' => create_function('$row',
                        'return "<a href=\"".url($row["page_name"])."\">".$row["title"]."</a>";'),
            ),
    );
    if ($user_id !== null) {
        function format_score_column($val)
        {
            if ($val === null) {
                $val = 0;
            }
            log_assert(is_whole_number($val));
            if ($val == 100) {
                return '100 !!!';
            } else {
                return $val;
            }
        }
        
        $column_infos[] = array (
                'title' => 'Scorul tau',
                'key' => 'score',
                'valform' => 'format_score_column',
        );
    }

    return format_table($tasks, $column_infos, $options);
}

?>
