<?php
// This module implements round and round-param related stuff.

// Get valid round types.
function round_get_types() {
    return array('classic');
}

// Get parameter infos.
function round_get_parameter_infos() {
    return array(
            'classic' => array(
                    'starttime' => array(
                            'description' => "Momentul la care incepe concursul (in format YYYY-MM-DD HH:MM:SS)",
                            'default' => '2000-10-10 07:00:00',
                            'type' => 'string',
                            'name' => 'Incepe la',
                    ),
                    'endtime' => array(
                            'description' => "Momentul la care se termina concursul (in format YYYY-MM-DD HH:MM:SS)",
                            'default' => '2000-10-10 10:00:00',
                            'type' => 'string',
                            'name' => 'Se termina',
                    )
            )
    );
}

// Valideaza parametrii. Returneaza errorile sub conventie de $form_errors.
function round_validate_parameters($round_type, $parameters) {
    $errors = array();
    if ($round_type == 'classic') {

        // Check start time.
        if (!is_db_date($parameters['starttime'])) {
            $errors['starttime'] = "Momentul de inceput trebuie specificat in format YYYY-MM-DD HH:MM:SS.";
            $start_tstamp = false;
        } else {
            $start_tstamp = db_date_parse($parameters['starttime']);
        }

        // Check end time.
        if (!is_db_date($parameters['endtime'])) {
            $errors['endtime'] = "Momentul de sfarsit trebuie specificat in format YYYY-MM-DD HH:MM:SS.";
            $end_tstamp = false;
        } else {
            $end_tstamp = db_date_parse($parameters['endtime']);
        }

        // Check start time < end time.
        if ($start_tstamp !== false && $end_tstamp !== false &&
                ($start_tstamp > $end_tstamp)) {
            $errors['endtime'] = 'Sfarsitul trebuie sa fie dupa inceput.';
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
            'page_name' => TB_ROUND_PREFIX . $round_id,
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

    if (!in_array(getattr($round, 'type', ''), round_get_types())) {
        $errors['type'] = "Tipul rundei este invalid";
    }

    return $errors;
}

?>
