<?php

require_once(IA_ROOT . "common/db/round.php");
require_once(IA_ROOT . "common/db/task.php");
require_once(IA_ROOT . "common/db/job.php");

// Big bad submit controller.
function controller_submit() {
    if (identity_anonymous()) {
        flash_error('Mai intai trebuie sa te autentifici.');
        redirect(url_login());
    }

    $values = array();
    $errors = array();

    if (request_is_post()) {
        $values = array(
            'task_id' => getattr($_POST, 'task_id'),
            'compiler_id' => getattr($_POST, 'compiler_id')
        );

        // Check task
        if ((!is_task_id($values['task_id'])) ||
            (!$task = task_get($values['task_id']))) {
            $errors['task_id'] = 'Alege problema la care doresti sa trimiti '
                                 .'solutie.';
        } else {
            // require permissions
            identity_require('task-submit', $task);

            // Check compiler.
            $valid_compilers = array('c', 'cpp', 'fpc');
            if (array_search($values['compiler_id'], $valid_compilers) === false) {
                $errors['compiler_id'] = 'Compilator invalid!';
            }

            // Check uploaded solution
            if (isset($_FILES['solution'])) {
                if (is_uploaded_file($_FILES['solution']['tmp_name'])) {
                    if (IA_SUBMISSION_MAXSIZE >= $_FILES['solution']['size']) {
                        $file_path = $_FILES['solution']['tmp_name'];
                        $file_buffer = file_get_contents($file_path);
                    }
                    else {
                        $errors['solution'] =
                            'Fisierul atasat depaseste dimensiunea maxima '.
                            'admisa: '.((int)IA_SUBMISSION_MAXSIZE/1024).'KB!';
                    }
                } else {
                    $errors['solution'] = '
                        Fisierul atasat nu a putut fi citit! Incearca din nou.
                        Daca problema persista te rugam sa <a href="' .
                        htmlentities(url_textblock('contact')).'">contactezi administratorul</a>.';
                }
            } else {
                $errors['solution'] = 'Ataseaza fisierul solutie.';
            }
        }

        // The end.
        if ($errors) {
            flash_error('NU am salvat solutia trimisa! Unul sau mai multe campuri
                         nu au fost completate corect.');
        } else {
            job_create($task['id'], identity_get_user_id(),
                       $values['compiler_id'], $file_buffer);
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

    $view = array(
            'title' => 'Trimite solutie',
            'tasks' => $tasks,
            'form_errors' => $errors,
            'form_values' => $values,
    );

    execute_view_die('views/submit.php', $view);
}

?>
