<?php

require_once(Config::ROOT . "www/format/table.php");
require_once(Config::ROOT . "www/format/format.php");
require_once(Config::ROOT . "common/db/score.php");
require_once(Config::ROOT . "common/db/round.php");
require_once(Config::ROOT . "common/security.php");

function macro_rankingsacmtotal($args) {
    $round_ids = getattr($args, 'rounds');

    $round_param = preg_split('/\s*\|\s*/', $round_ids);
    $rounds = array();
    foreach ($round_param as $param) {
        $round = preg_split('/\s*\:\s*/', $param);
        if (!identity_can('round-view-scores', round_get($round[0]))) {
            continue;
        }
        array_push($rounds, array(
            'round_id' => $round[0],
            'round_name' => getattr($round, 1, ' ')
        ));
    }

    $round_rankings = array();
    foreach ($rounds as $round) {
        $round_rankings[$round['round_id']] =
            score_get_rankings_acm($round['round_id'], true, false);
    }

    $users = array();
    foreach ($round_rankings as $round_id => $round) {
        foreach ($round as $row) {
            $users[$row['user_id']] = array(
                'username' => $row['username'],
                'fullname' => $row['fullname'],
                'rating' => $row['rating']);
        }
    }

    foreach ($users as &$user) {
        $user['score'] = 0;
        $user['penalty'] = 0;
        foreach ($rounds as $round) {
            $user[$round['round_id']] = array('score' => 0, 'penalty' => 0);
        }
    }

    foreach ($round_rankings as $round_id => $round) {
        foreach ($round as $row) {
            $users[$row['user_id']][$round_id]['penalty'] +=
                $row['penalty'];
            $users[$row['user_id']]['penalty'] += $row['penalty'];

            $users[$row['user_id']][$round_id]['score'] +=
                $row['score'];
            $users[$row['user_id']]['score'] += $row['score'];
        }
    }

    $rankings = array();
    $scores = array();
    $penalties = array();
    foreach ($users as $user) {
        $rankings[] = $user;
        $scores[] = $user['score'];
        $penalties[] = $user['penalty'];
    }

    if (count($rankings) <= 0) {
        return macro_message('Nici un rezultat înregistrat pentru această rundă.');
    }

    array_multisort($scores, SORT_DESC, $penalties, SORT_ASC, $rankings);
    for ($i = 0; $i < count($rankings); ++$i) {
        if ($i == 0 ||
            ($rankings[$i - 1]['score'] != $rankings[$i]['score'] ||
             $rankings[$i - 1]['penalty'] != $rankings[$i]['penalty'])) {
            $rankings[$i]['ranking'] = $i + 1;
        } else {
            $rankings[$i]['ranking'] = $rankings[$i - 1]['ranking'];
        }
    }

    $row = null;
    $column_infos = array(
        array(
            'title' => 'Poziție',
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

    foreach ($rounds as $round) {
        $column_infos[] = array(
            'title' => $round['round_name'],
            'key' => $round['round_id'],
            'css_class' => 'number score',
            'rowform' => function ($row) use ($round) {
                $info = $row[$round['round_id']];
                $score = getattr($info, 'score', 0);
                $penalty = getattr($info, 'penalty', 0);
                if ($score > 0) {
                    $score += 1;
                }

                return format_acm_score(1, $penalty, $score);
            });
    }

    $column_infos[] = array(
        'title' => 'Total',
        'key' => 'total',
        'css_class' => 'number score',
        'rowform' => function ($row) {
            $score = getattr($row, 'score', 0);
            if ($score > 0) {
                $score += 1;
            }
            $penalty = getattr($row, 'penalty', 0);
            return format_acm_score(1, $penalty, $score);
        }
    );

    $options = array('css_class' => 'sortable');
    return format_table($rankings, $column_infos, $options);
}
