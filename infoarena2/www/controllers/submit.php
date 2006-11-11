<?php

// Big bad submit controller.
function controller_submit() {
    global $identity_user;

    if (identity_anonymous()) {
        flash_error('Va rugam sa va autentificati mai intai');
        redirect(url('login'));
    }

    $action = request("action");
    if ('save' == $action) {
        $form_values = array(
            'task_id' => getattr($_POST, 'task_id'),
            'compiler_id' => getattr($_POST, 'compiler_id')
        );
        $form_errors = array();

        // Check task
        $task = task_get($form_values['task_id']);
        if (!$task) {
            $form_errors['task_id'] = 'Va rugam sa alegeti problema la care doriti sa '
                                      . 'trimiteti solutie.';
        }
        else {
            // require permissions
            identity_require('task-submit', $task);

            // make sure user is already registered to at least one round that includes this task
            $rounds = task_get_parent_rounds($task['id']);
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
            }

            // Check compiler.
            if ('output-only'!=$task['type'] && (false===array_search($form_values['compiler_id'], array('c', 'cpp', 'fpc')))) {
                $form_errors['compiler_id'] = 'Compilator invalid!';
            }

            // Check uploaded solution
            if (isset($_FILES['solution'])) {
                if (is_uploaded_file($_FILES['solution']['tmp_name'])) {
                    if (IA_SUBMISSION_MAXSIZE >= $_FILES['solution']['size']) {
                        $filepath = $_FILES['solution']['tmp_name'];
                        $file_buffer = file_get_contents($filepath);
                    }
                    else {
                        $form_errors['solution'] =
                            'Fisierul atasat depaseste dimensiunea maxima admisa!';
                    }
                }
                else {
                    $form_errors['solution'] = '
                        Fisierul atasat nu a putut fi citit! Incercati din nou.
                        Daca problema persista va rugam sa <a href="' .
                        url('Contact') . '">contactati administratorul</a>.';
                }
            }
            else {
                $form_errors['solution'] = 'Va rugam sa atasati fisierul solutie.';
            }
        }

        // The end.
        if ($form_errors) {
            flash_error('NU am salvat solutia trimisa! Unul sau mai multe campuri
                         nu au fost completate corect.');
        }
        else {
            job_create($task['id'], getattr($identity_user, 'id'),
                       $form_values['compiler_id'], $file_buffer);
            // no errors => save submission
            flash('Solutia a fost salvata.');
            $url = getattr($_SERVER, 'HTTP_REFERER');
            if (!$url) {
                $url = url('submit');
            }
            redirect($url);
        }
        // Fall through to submit form.
    }
    else {
        $form_errors = array();
        $form_values = array();
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
            'form_errors' => $form_errors,
            'form_values' => $form_values,
    );

    execute_view_die('views/submit.php', $view);
}

?>
