<?php

require_once(IA_ROOT . "common/db/round.php");
require_once(IA_ROOT . "common/db/task.php");
require_once(IA_ROOT . "common/db/job.php");

// Big bad submit controller.
function controller_submit() {
    if (identity_anonymous()) {
        flash_error('Va rugam sa va autentificati mai intai');
        redirect(url_login());
    }

    $values = array();
    $errors = array();

    if (request_is_post()){
        $values = array(
            'task_id' => getattr($_POST, 'task_id'),
            'compiler_id' => getattr($_POST, 'compiler_id')
        );

        // Check task
        if ((!is_task_id($values['task_id'])) ||
            (!$task = task_get($values['task_id']))) {
            $errors['task_id'] = 'Va rugam sa alegeti problema la care doriti sa '
                                      . 'trimiteti solutie.';
        } else {
            // require permissions
            identity_require('task-submit', $task);

            // make sure user is already registered to at least one round that includes this task
            // FIXME: registration features disabled.
            /* $rounds = task_get_parent_rounds($task['id']);
            $registered = false;
            foreach ($rounds as $round_id) {
                if (round_is_registered($round_id, $identity_user['id']))  {
                    $registered = true;
                    break;
                }
            }
            if (!$registered) {
                $form_errors['task_id'] = 'Inscrie-te mai intai intr-o runda '
                                          .'pentru a trimite solutii la aceasta problema';
            }*/

            $valid_compilers = array('c', 'cpp', 'fpc');
            // Check compiler.
            if ('output-only' != $task['type'] &&
                (false === array_search($values['compiler_id'], $valid_compilers))) {
                $form_errors['compiler_id'] = 'Compilator invalid!';
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
                            'Fisierul atasat depaseste dimensiunea maxima admisa!';
                    }
                } else {
                    $errors['solution'] = '
                        Fisierul atasat nu a putut fi citit! Incercati din nou.
                        Daca problema persista va rugam sa <a href="' .
                        url('Contact') . '">contactati administratorul</a>.';
                }
            } else {
                $errors['solution'] = 'Va rugam sa atasati fisierul solutie.';
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
    $tasks_unfiltered = task_list_info();
    $tasks = array();
    foreach ($tasks_unfiltered as $k => $t) {
        if (identity_can('task-submit', $t)) {
            $tasks[$k] = $t;
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
