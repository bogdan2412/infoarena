<?php
// This module implements round and round-param related stuff.

// Get valid round types.
function round_get_types() {
    return array(
            'classic' => 'Concurs clasic',
            'archive' => 'Arhiva de pregatire',
    );
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
            'archive' => array(
            ),
    );
}

// Validate parameters. Return erros as $form_errors convention.
function round_validate_parameters($round_type, $parameters) {
    $errors = array();
    if ($round_type == 'classic') {
        // Check duration
        $duration = getattr($parameters, 'duration');
        if (is_null($duration)) {
            $errors['duration'] = "Durata trebuie specificata";
        }
        if (!is_numeric($duration) || $duration < 0) {
            $errors['duration'] = "Durata trebuie sa fie un numar pozitiv";
        }
    } elseif ($round_type == 'archive') {
        // Empty...
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
            'page_name' => TB_ROUND_PREFIX . $round_id,
            'state' => 'waiting',
            'start_time' => NULL
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

    // NULL is ok here.
    if (!is_db_date(getattr($round, 'start_time', db_date_format()))) {
        $errors['start_time'] = "Timpul trebuie specificat caYYYY-MM-DD HH:MM:SS";
    }

    return $errors;
}

// Called by the eval when a round starts.
function round_event_start($round) {
    log_assert_valid(round_validate($round));
    log_print("CONTEST LOGIC: Starting round {$round['id']}.");
    $round['state'] = 'running';
    round_update($round);
    round_unhide_all_tasks($round['id']);
}

// Called when a round is stopped.
function round_event_stop($round) {
    log_assert_valid(round_validate($round));
    log_print("CONTEST LOGIC: Stopping round {$round['id']}.");
    $round['state'] = 'complete';
    round_update($round);
}

?>
