<?php
// This module implements round and round-param related stuff.

// Get valid round types.
function round_get_types() {
    return array(
            'classic' => 'Concurs clasic',
            'penalty-round' => 'Concurs cu penalizare',
            'archive' => 'Arhiva de pregatire',
            'user-defined' => 'Concurs virtual',
            'acm-round' => 'Concurs tip ACM'
    );
}

/**
 * Get valid contest type rounds
 * Those which use private tasks
 *
 * @return array
 */
function round_get_contest_types() {
    return array('classic', 'penalty-round', 'acm-round');
}

// Get parameter infos.
function round_get_parameter_infos() {
    return array(
            'classic' => array(
                    'duration' => array(
                            'name' => 'Durata',
                            'description' => "Durata concursului, in ore",
                            'default' => '4.5',
                            'type' => 'float',
                    ),
                    'rating_update' => array(
                            'name' => 'Afecteaza rating-urile',
                            'description' => "Daca rezultatele din acest concurs ".
                                "afecteaza rating-urile concurentilor",
                            'default' => '1',
                            'type' => 'bool',
                    ),
                ),
            'penalty-round' => array(
                    'duration' => array(
                            'name' => 'Durata',
                            'description' => 'Durata concursului, in ore',
                            'default' => '2',
                            'type' => 'float',
                    ),
                    'rating_update' => array(
                            'name' => 'Afecteaza rating-urile',
                            'description' => 'Daca rezultatele din acest concu'
                                . 'rs afecteaza rating-urile concurentilor',
                            'default' => '1',
                            'type' => 'bool',
                    ),
                    'decay_period' => array(
                            'name' => 'Perioada de pierdere a unui procent din'
                                . ' punctaj',
                            'description' => 'Intervalul de timp(in secunde) '
                                . 'in care un concurent pierde 1 procent din '
                                . 'punctajul sau pe problema',
                            'default' => '120',
                            'type' => 'integer',
                    ),
                    'submit_cost' => array(
                            'name' => 'Procentajul pierdut pe submisie',
                            'description' => 'Procentul din punctaj pierdut pe'
                                . ' submisii ulterioare',
                            'default' => '3',
                            'type' => 'integer',
                    ),
                    'minimum_score' => array(
                            'name' => 'Procentul minim pe problema',
                            'description' => 'Procentul minim din punctaj pe c'
                                . 'are il poate obtine un concurent pe o probl'
                                . 'ema rezolvata corect',
                            'default' => '50',
                            'type' => 'integer',
                    ),
                ),
            'acm-round' => array(
                    'duration' => array(
                        'name' => 'Durata',
                        'description' => 'Durata concursului, in ore',
                        'default' => '5',
                        'type' => 'float'
                    ),
                    'scoreboard-duration' => array(
                        'name' => 'Durata vizibilitatii clasamentului',
                        'description' => 'Durata vizibilitatii clasamentului, '
                            . 'in ore',
                        'default' => '4',
                        'type' => 'float'
                    )
                ),
            'archive' => array(
                    'duration' => array(
                            'name' => 'Durata',
                            'description' => "Infinit. Nu schimba.",
                            'default' => '10000000',
                            'type' => 'float',
                    ),
                ),
            'user-defined' => array(
                'duration' => array(
                            'name' => 'Durata',
                            'description' => "Durata concursului, in ore",
                            'default' => '4.5',
                            'type' => 'float',
                    ),
                ),
            );
}

// Validate parameters. Return erros as $form_errors convention.
function round_validate_parameters($round_type, $parameters) {
    $errors = array();
    if ($round_type == 'classic' || $round_type == 'user-defined'
            || $round_type == 'archive'
            || $round_type == 'penalty-round'
            || $round_type == 'acm-round') {
        // Check duration
        $duration = getattr($parameters, 'duration');
        if (is_null($duration)) {
            $errors['duration'] = "Durata trebuie specificata";
        }

        if ($round_type == 'user-defined') {
            if ($duration > IA_USER_DEFINED_ROUND_DURATION_LIMIT) {
                $errors['duration'] = "Durata maxim admisa este de " .
                    IA_USER_DEFINED_ROUND_DURATION_LIMIT . " ore";
            }
        }

        if ($round_type == 'acm-round') {
            $scoreboard_duration = getattr($parameters, 'scoreboard-duration');
            if (is_null($scoreboard_duration)) {
                $errors['duration'] = "Durata vizibilitatii clasamentului "
                    . "trebuie specificata";
                return $errors;
            }

            if (!is_numeric($scoreboard_duration) ||
                $scoreboard_duration < 0) {
                $errors['duration'] = "Durata vizibilitatii clasamentului "
                                    . "trebuie sa fie un numar pozitiv";
                return $errors;
            }
        }

        if (!is_numeric($duration) || $duration < 0) {
            $errors['duration'] = "Durata trebuie sa fie un numar pozitiv";
        }
    } else {
        log_error("Bad round_type");
    }

    return $errors;
}

// Initialize a round object
function round_init($round_id, $round_type, $user = null) {
    $round = array(
            'id' => $round_id,
            'type' => $round_type,
            'title' => $round_id,
            'page_name' => IA_ROUND_TEXTBLOCK_PREFIX . $round_id,
            'state' => 'waiting',
            'start_time' => NULL,
            'public_eval' => (($round_type == 'archive') ? 1 : 0),
            'user_id' => $user['id']
    );

    log_assert_valid(round_validate($round));
    return $round;
}

// Validates a round.
// NOTE: this might be incomplete, so don't rely on it exclusively.
// Use this to check for a valid model. It's also usefull in controllers.
function round_validate($round) {
    $errors = array();

    // If you can't pass a fucking array you don't deserve to live.
    log_assert(is_array($round), "You didn't even pass an array");

    if (!is_round_id(getattr($round, 'id', ''))) {
        $errors['id'] = 'ID de runda invalid';
    }

    if (!is_page_name($round['page_name'])) {
        $errors['page_name'] = "Homepage invalid";
    }

    if (!array_key_exists(getattr($round, 'type'), round_get_types())) {
        $errors['type'] = "Tipul rundei este invalid";
    }

    if (!in_array(getattr($round, 'state'),
            array('running', 'waiting', 'complete'))) {
        $errors['state'] = "Starea rundei este invalida";
    }

    if (!is_whole_number(getattr($round, 'public_eval'))) {
        $errors['public_eval'] = "public_eval este invalid";
    }

    // NULL is ok here.
    if (!is_db_date(getattr($round, 'start_time', db_date_format()))) {
        $errors['start_time'] = "Timpul trebuie specificat ca YYYY-MM-DD HH:MM:SS";
    } else {
        if ($round['type'] == 'user-defined') {
            $current_time = db_date_parse(date("Y-m-d H:i:s"));
            $round_time = db_date_parse($round['start_time']);

            if (($round_time - $current_time) >
                    IA_USER_DEFINED_ROUND_DAYSBEFORE_LIMIT * 60 * 60 * 24) {
                $errors['start_time'] = "Nu poti creea o runda cu mai mult de "
                    . IA_USER_DEFINED_ROUND_DAYSBEFORE_LIMIT . " zile inainte";
            }
        }
    }

    if (!is_user_id(getattr($round, 'user_id', ''))) {
        $errors['user_id'] = "ID-ul userului este invalid";
    }

    return $errors;
}

// Called by the eval when a round starts.
function round_event_start($round) {
    log_assert_valid(round_validate($round));
    log_print("CONTEST LOGIC: Starting round {$round['id']}.");
    $round['state'] = 'running';
    round_update($round);
    // User defined rounds always contain already visible tasks.
    if (in_array($round['type'], round_get_contest_types())) {
        round_unhide_all_tasks($round['id']);
    }
}

// Called when a round is stopped.
function round_event_stop($round) {
    log_assert_valid(round_validate($round));
    log_print("CONTEST LOGIC: Stopping round {$round['id']}.");
    $round['state'] = 'complete';
    // Results should be immediately visible after a round ends
    // if its type is not classic or acm-round.
    // FIXME: make this a parameter
    if ($round["type"] != "classic" && $round["type"] != "acm-round") {
        $round['public_eval'] = 1;
    }
    round_update($round);
}

// Called when a round should be in waiting.
function round_event_wait($round) {
    log_assert_valid(round_validate($round));
    log_print("CONTEST LOGIC: Stand-by for round {$round['id']}.");
    $round['state'] = 'waiting';
    round_update($round);
    // User defined rounds always contain already visible tasks.
    // If such a round is postponed, do not (re)hide the tasks
    // like for classic rounds, where the tasks are not visible
    // before the round starts.
    if (in_array($round['type'], round_get_contest_types())) {
        round_hide_all_tasks($round['id']);
    }
}
