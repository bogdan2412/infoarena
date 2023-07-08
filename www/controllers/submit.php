<?php

require_once(Config::ROOT . "common/job.php");

// Big bad submit controller.
function controller_submit() {
    identity_require_login();

    $view = array(
        'title' => 'Trimite soluție',
    );

    $values = array();
    $errors = array();

    if (request_is_post()) {
        $values = array(
            'task_id' => request('task_id'),
            'compiler_id' => request('compiler_id'),
            'round_id' => request('round_id'),
            'remote_ip_info' => remote_ip_info(),
        );

        // Check uploaded solution
        if (isset($_FILES['solution']) && is_uploaded_file($_FILES['solution']['tmp_name'])) {
            $values['solution'] = file_get_contents($_FILES['solution']['tmp_name']);
        }

        identity_require_login();
        $errors = safe_job_submit($values, identity_get_user());

        // The end.
        if (!isset($errors["round_id"])) {
            $_SESSION["_ia_last_submit_round"] = $values["round_id"];
        }

        if (isset($errors['submit_limit']) && count($errors) == 1) {
            FlashMessage::addError($errors['submit_limit']);
            redirect(getattr($_SERVER, 'HTTP_REFERER', url_submit()));
        }

        if ($errors) {
            FlashMessage::addError('NU am salvat soluția trimisă. Unul sau mai multe câmpuri
                         nu au fost completate corect.');
        } else {
            FlashMessage::addSuccess('Am salvat soluția.');
            redirect(getattr($_SERVER, 'HTTP_REFERER', url_submit()));
        }
        // Fall through to submit form.
    }

    // get task list.
    // FIXME: proper filter?
    $tasks = array();
    foreach (task_get_all() as $t) {
        if (identity_can('task-submit', $t)) {
            $tasks[$t['id']] = $t;
        }
    }

    $view['tasks'] = $tasks;
    $view['form_errors'] = $errors;
    $view['form_values'] = $values;

    execute_view_die('views/submit.php', $view);
}
