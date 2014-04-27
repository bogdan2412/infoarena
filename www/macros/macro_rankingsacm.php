<?php

require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "common/db/score.php");
require_once(IA_ROOT_DIR . "common/db/round.php");
require_once(IA_ROOT_DIR . "common/security.php");

function macro_rankingsacm($args) {
    $round_id = getattr($args, 'round');

    if (!$round_id) {
        return macro_error("Parameter 'round' is required.");
    }

    $round = round_get($round_id);
    if (!$round || getattr($round, 'type') != 'acm-round') {
        return macro_error("Nu exista o runda tip acm cu acest nume.");
    }

    $row = null;
    $column_infos = array(
        array(
            'title' => 'Pozitie',
            'key' => 'ranking',
            'css_class' => 'number rank'
        ),
        array(
            'title' => 'Nume',
            'key' => 'fullname',
            'rowform' => function($row) {
                return format_user_normal($row['username'],
                                          $row['fullname'],
                                          $row['rating']);
            },
        )
    );

    $tasks = round_get_tasks($round_id);

    foreach ($tasks as $task) {
        array_push($column_infos, array(
            'title' => $task['title'],
            'key' => $task['id'],
            'rowform' => function($row) use ($task) {
                $info = $row[$task['id']];
                $score = getattr($info, 'score', 0);
                $penalty = getattr($info, 'penalty', 0);
                $submission = getattr($info, 'submission', 0);
                return format_acm_score($score, $penalty, $submission);
            },
            'css_class' => 'number score'
        ));
    }

    array_push($column_infos, array(
        'title' => 'Scor',
        'key' => 'score',
        'rowform' => function($row) {
            return round($row['score']);
        },
        'css_class' => 'number score'
    ));

    array_push($column_infos, array(
        'title' => 'Penalizare',
        'key' => 'penalty',
        'rowform' => function($row) {
            return round($row['penalty']);
        },
        'css_class' => 'number score'
    ));

    $rankings = array();
    if (identity_can('round-view-scores', $round)) {
        $rankings = score_get_rankings_acm($round_id, true);
    } else if (identity_can('round-acm-view-partial-scores', $round)) {
        $rankings = score_get_rankings_acm($round_id, false);
    }

    if (count($rankings) <= 0) {
        return macro_message('Nici un rezultat inregistrat pentru aceasta ' .
                             'runda.');
    }

    $options = array('css_class' => 'sortable');
    return format_table($rankings, $column_infos, $options);
}
