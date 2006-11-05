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
        if (!is_datetime($parameters['starttime'])) {
            $errors['starttime'] = "Momentul de inceput trebuie specificat in format YYYY-MM-DD HH:MM:SS.";
        }
        else {
            $tstamp = parse_datetime($parameters['starttime']);
            if ($tstamp < time()) {
                $errors['starttime'] = 'Momentul de inceput trebuie sa fie in viitor.';
            }
        }

        // FIXME: Make sure endtime is in the future, greater than starttime
        if (!is_datetime($parameters['endtime'])) {
            $errors['endtime'] = "Momentul de sfarsit trebuie specificat in format YYYY-MM-DD HH:MM:SS.";
        }
        else {
            $tstamp = parse_datetime($parameters['endtime']);
            $tstamp0 = is_datetime($parameters['starttime']) ? parse_datetime($parameters['starttime']) : false;

            if ($tstamp < time()) {
                $errors['endtime'] = 'Momentul de sfarsit trebuie sa fie in viitor.';
            }
            else if ($tstamp0 && ($tstamp < $tstamp0)) {
                $errors['endtime'] = 'Sfarsitul trebuie sa fie dupa inceput. ;)';
            }
        }
    }
    else {
        log_error("Bad round_type");
    }

    return $errors;
}

?>
