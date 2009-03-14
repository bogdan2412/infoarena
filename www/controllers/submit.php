<?php

require_once(IA_ROOT_DIR . "common/job.php");

// Big bad submit controller.
function controller_submit() {
    identity_require_login();

    $view = array(
        'title' => 'Trimite solutie',
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
        if ($errors) {
            flash_error('NU am salvat solutia trimisa! Unul sau mai multe campuri
                         nu au fost completate corect.');
        } else {
            flash('Solutia a fost salvata.');
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

?>
