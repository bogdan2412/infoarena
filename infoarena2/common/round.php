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
                            'default' => '',
                            'type' => 'datetime',
                            'name' => 'Incepe la',
                    ),
                    'endtime' => array(
                            'description' => "Momentul la care se termina concursul (in format YYYY-MM-DD HH:MM:SS)",
                            'default' => '',
                            'type' => 'datetime',
                            'name' => 'Se termina',
                    )
            )
    );
}

function round_get_parameter_infos_hack() {
    $infos = round_get_parameter_infos();
    $ret = array_merge($infos['classic']);
    return $ret;
}

// Valideaza parametrii. Returneaza errorile sub conventie de $form_errors.
function round_validate_parameters($round_type, $parameters) {
    $errors = array();
    if ($round_type == 'classic') {

        // Check start time.
        if (!is_datetime($parameters['starttime'])) {
            $errors['starttime'] = "Momentul de inceput trebuie specificat in format YYYY-MM-DD HH:MM:SS.";
            $start_tstamp = false;
        } else {
            $start_tstamp = parse_datetime($parameters['starttime']);
        }

        // Check end time.
        if (!is_datetime($parameters['endtime'])) {
            $errors['endtime'] = "Momentul de sfarsit trebuie specificat in format YYYY-MM-DD HH:MM:SS.";
            $end_tstamp = false;
        } else {
            $end_tstamp = parse_datetime($parameters['endtime']);
        }

        // Check start time < end time.
        if ($start_tstamp && $end_tstamp && ($tstamp < $tstamp0)) {
            $errors['endtime'] = 'Sfarsitul trebuie sa fie dupa inceput.';
        }
    } else {
        log_error("Bad round_type");
    }

    return $errors;
}

?>
