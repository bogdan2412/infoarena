<?php

require_once(IA_ROOT_DIR . "www/format/list.php");
require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "common/db/round.php");
require_once(IA_ROOT_DIR . "common/db/task.php");
require_once(IA_ROOT_DIR . "common/db/user.php");
require_once(IA_ROOT_DIR . "common/round.php");
require_once(IA_ROOT_DIR . "www/macros/macro_stars.php");

function format_score_column($val) {
    if (is_null($val)) {
        return 'N/A';
    } else {
        return round($val);
    }
}

function format_progress_column($val) {
    if (is_null($val)) {
        return 'N/A';
    } else {
        return $val;
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

function format_title($row) {
    $title = "<span style=\"float:left;\">".format_link(url_textblock($row["page_name"]), $row["title"])."</span>";
    if ($row['open_tests']) {
        $title .= "<span style=\"float:right;\">";
        $title .= format_link(url_task($row['id']), format_img(url_static("images/open_small.png"), ""), false);
        $title .= "</span>";
    }
    return $title;
}

function format_single_author($tag) {
    return format_link(url_task_search(array($tag["id"])), $tag["name"]);
}

function format_author($row) {
    $authors = task_get_authors($row['id']);
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

/**
 * Returns whether or not an user received a score different than 0
 * For use in penalty-round tasks
 * @param array $row   the task in the round for which the above question
 *                     is asked
 * @return string
 */
function task_row_style_absolute($row) {
    $score = getattr($row, 'score');
    if (is_null($score)) {
        return '';
    }

    log_assert(is_numeric($score));
    $score = (int)$score;

    if ($score > 0) {
        return 'solved';
    } else {
        return 'tried';
    }
}

function task_list_tabs($round_page, $active, $as_user) {
    $tabs = array();

    $tab_names = array(IA_TLF_ALL => 'Toate problemele',
                       IA_TLF_UNSOLVED => 'Nerezolvate',
                       IA_TLF_TRIED => 'Încercate',
                       IA_TLF_SOLVED => 'Rezolvate');

    foreach ($tab_names as $id => $text) {
        $args = array();
        $args['filtru'] = $id;
        if ($as_user) {
            $args['user'] = $as_user;
        }

        $tabs[$id] = format_link(url_complex($round_page, $args), $text);
    }
    $tabs[$active] = array($tabs[$active], array('class' => 'active'));
    return format_ul($tabs, 'htabs');
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
    $options['show_count'] = getattr($args, 'show_count', true);
    $options['show_display_entries'] = getattr($args, 'show_display_entries', false);

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

    $scores = getattr($args, 'score') && identity_can("round-view-scores", $round);
    $filter_user = null;
    if (identity_is_anonymous() && $scores && request('user', '')) {
        $filter_user = user_get_by_username(request('user', ''));
        $user_id = null;
        if($filter_user != null) {
            $filter_user_id = $filter_user['id'];
        } else {
            $filter_user_id = null;
        }
    } else if (identity_is_anonymous() || $scores == false) {
        $user_id = null;
        $filter_user_id = null;
    } else {
        $user_id = identity_get_user_id();
        $filter_username = request('user', identity_get_username());
        $filter_user = user_get_by_username($filter_username);
        if ($filter_user != null) {
            $filter_user_id = $filter_user['id'];
        } else {
            $filter_user_id = $user_id;
        }
    }

    $display_tabs = getattr($args, 'show_filters');
    if (is_null($display_tabs) && $round["type"] == "archive") {
        $display_tabs = "true";
    }

    $filter = request('filtru', '');
    $tabs = '';
    if ($filter_user_id
        && $display_tabs == 'true'
        && identity_can('round-view-scores', $round)) {
        $tabs = task_list_tabs(
            $round['page_name'],
            $filter,
            $filter_user['username']);
    } else {
        $filter = '';
    }

    $show_numbers = getattr($args, 'show_numbers', false);
    $show_authors = getattr($args, 'show_authors', true);
    $show_sources = getattr($args, 'show_sources', true);
    $show_ratings = getattr($args, 'show_ratings', false);
    $show_progress = getattr($args, 'show_progress', false) &&
                     identity_can("round-view-progress", $round);

    // get round tasks
    $tasks = round_get_tasks(
             $round_id,
             $options['first_entry'],
             $options['display_entries'],
             $filter_user_id,
             $scores,
             $filter,
             $show_progress);

    $options['total_entries'] = round_get_task_count(
             $round_id, $user_id, $filter);

    if (getattr($args, 'absolute_style') !== null) {
        $options['row_style'] = 'task_row_style_absolute';
    } else {
        $options['row_style'] = 'task_row_style';
    }
    $options['css_row_parity'] = true;

    $options['css_class'] = 'tasks sortable';
    if (getattr($args, 'drag_and_drop', false))
      $options['css_class'] .= ' dragndrop';

    $column_infos = array();
    if ($show_numbers) {
        $column_infos[] = array(
                'title' => 'Număr',
                'css_class' => 'number',
                'rowform' => function($row) {
                    return str_pad($row["order"] - 1, 3, '0', STR_PAD_LEFT);
                },
        );
    }
    $column_infos[] = array(
            'title' => 'Titlul problemei',
            'css_class' => 'task',
            'rowform' => 'format_title'
    );
    if ($show_authors) {
        $column_infos[] = array(
                'title' => 'Autor',
                'css_class' => 'author',
                'rowform' => 'format_author',
        );
    }
    if ($show_sources) {
        $column_infos[] = array(
                'title' => 'Sursă',
                'css_class' => 'source',
                'key' => 'source',
        );
    }
    if ($show_ratings) {
        $column_infos[] = array(
                'html_title' => 'Dificultate',
                'title_css_class' => 'new_feature',
                'css_class' => 'rating',
                'key' => 'rating',
                'valform' => 'format_rating_column',
        );
    }
    if (!is_null($user_id)) {
        $column_infos[] = array (
                'title' => 'Scorul tău',
                'css_class' => 'score',
                'key' => 'score',
                'valform' => 'format_score_column',
        );
    }
    if ($show_progress) {
        $column_infos[] = array (
                'title' => 'Note',
                'css_class' => 'number',
                'key' => 'progress',
                'valform' => 'format_progress_column',
        );
    }

    $as_user = '';
    if ($round['type'] == 'archive') {
        $pager_hidden_fields = '';
        foreach (pager_init_options($args) as $option => $value) {
            $pager_hidden_fields .=
                '<input type="hidden" name="'.$option.'" value="'.$value.'"/>';
        }
        if ($filter) {
            $pager_hidden_fields .=
                '<input type="hidden" name="filtru" value="'.$filter.'"/>';
        }

        $as_user .=
            '<span>Vezi această listă din perspectiva altui utilizator: '
            .'<form method="GET" style="display:inline">'
            .'  <input type="text" placeholder="GavrilaVlad" name="user"'
            .'      value="'.request('user', '').'"/>'
            .$pager_hidden_fields
            .'  <input style="display:inline-block"'
            .'         type="submit" value="Vezi" class="button" />'
            .'</form>'
            .'</span><BR>';
    }

    if ($filter_user_id !== $user_id) {
        $as_user .=
            '<span>Momentan vezi această listă de probleme din '
            .'perspectiva utilizatorului '
            .format_user_tiny(
                $filter_user['username'],
                $filter_user['full_name'],
                $filter_user['rating_cache'])
            .'</span><BR>';
    } else if (request('user') && request('user') != identity_get_username()) {
        $as_user .=
            '<span style="color: red;">Nu există un utilizator cu acest'
            .' username sau nu ai destule permisiuni.</span>';
    }

    if ($as_user) {
        $as_user =
            '<div style="display:block; margin-bottom:30px;">'
            .'  <div style="float:right; text-align: right;">'.$as_user.'</div>'
            .'</div>';
    }

    return $as_user.$tabs.format_table($tasks, $column_infos, $options);
}

?>
