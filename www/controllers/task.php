<?php

require_once(IA_ROOT_DIR . "common/db/task.php");
require_once(IA_ROOT_DIR . "common/task.php");
require_once(IA_ROOT_DIR . "common/tags.php");

// Displays form to either create a new task or edit an existing one.
// This form does not edit task content (its associated textblock)
// (textblock editor does that)
//
// Initially, the form is filled in with either:
//      * values for the existing task we edit
//      * default initial values when creating a new task
//
// Form submits to controller_task_save_details().
// When a validation error occurs in controller_task_save_details() it calls
// this controller as an error handler in order to display the form
// with the user-submitted data and their corresponding errors.
function controller_task_details($task_id) {
    // validate task_id
    if (!is_task_id($task_id)) {
        flash_error('Identificatorul de task este invalid');
        redirect(url_home());
    }

    // Get task
    $task = task_get($task_id);
    if (!$task) {
        flash_error("Problema nu exista");
        redirect(url_home());
    }

    // Security check
    identity_require('task-edit', $task);

    // get parameter list for tasks (in general, not for this specific task)
    $param_infos = task_get_parameter_infos();
    $task_params = task_get_parameters($task['id']);

    // Form stuff.
    $values = array();
    $errors = array();

    // Fill in form values from request, defaults in $task
    $fields = array('type', 'source', 'hidden', 'title', 'page_name',
                    'open_source', 'open_tests', 'test_count', 'test_groups',
                    'evaluator', 'use_ok_files', 'public_tests');

    foreach ($fields as $field) {
        $values[$field] = request($field, $task[$field]);
    }

    // Parameter values, for all possible types of tasks.
    // Yucky, but functional.
    foreach (task_get_types() as $task_type => $pretty_name) {
        foreach ($param_infos[$task_type] as $name => $info) {
            $form_name = "param_{$task_type}_{$name}";
            $def = $info['default'];
            if ($task_type == $task['type']) {
                $def = getattr($task_params, $name, $def);
            }
            $values[$form_name] = request($form_name, $def);
        }
    }

    // Tag data
    $tag_types = Array('author', 'contest', 'year', 'round', 'age_group');
    $tag_parents = Array('year' => 'contest', 'round' => 'year');

    // FIXME: tags that have children such as contest, year or round should have only one tag
    foreach ($tag_types as $type) {
        $values['tag_'.$type] = request('tag_'.$type,
            tag_build_list('task', $task_id, $type));
    }

    // Task owner
    if (identity_can('task-edit-owner', $task)) {
        if ($task["user_id"]) {
            $user = user_get_by_id($task["user_id"]);
            log_assert($user, "Task has invalid user_id");
            $username = $user["username"];
        } else {
            $username = "";
        }
        $values["user"] = request("user", $username);
    }

    // Validate the monkey.
    if (request_is_post()) {
        // Build new task
        $new_task = $task;
        foreach ($fields as $field) {
            $new_task[$field] = $values[$field];
        }

        if (identity_can('task-edit-owner', $task)) {
            if ($values["user"] == "") {
                $new_task["user_id"] = 0;
            } else {
                $user = user_get_by_username($values["user"]);
                if (!$user) {
                    $errors["user"] = "Utilizator inexistent";
                } else {
                    $new_task["user_id"] = $user["id"];
                }
            }
        }

        $task_errors = task_validate($new_task);
        $errors = $task_errors;

        // Check security.
        if ($new_task['hidden'] != $task['hidden']) {
            identity_require('task-change-security', $task);
        }

        // Handle task parameters. Only for current type, and only if
        // properly selected.
        $new_task_params = $task_params;
        if (!array_key_exists('type', $task_errors)) {
            $task_type = $new_task['type'];
            foreach ($param_infos[$task_type] as $name => $info) {
                $form_name = "param_{$task_type}_{$name}";
                $new_task_params[$name] = $values[$form_name];
            }
            $task_params_errors = task_validate_parameters(
                    $task_type, $new_task_params);
            // Properly copy errors. Sucky
            foreach ($param_infos[$task_type] as $name => $info) {
                $form_name = "param_{$task_type}_{$name}";
                if (array_key_exists($name, $task_params_errors)) {
                    $errors[$form_name] = $task_params_errors[$name];
                }
            }
        }

        // Handle tags
        foreach ($tag_types as $type) {
            tag_validate($values, $errors, $type,
                getattr($tag_parents, $type));
        }

        // If no errors then do the db monkey
        $tags = array();
        if (!$errors) {
            // FIXME: error handling? Is that even remotely possible in php?
            task_update($new_task);
            task_update_parameters($task_id, $new_task_params);

            if (identity_can('task-tag', $new_task)) {
                foreach ($tag_types as $type) {
                    $parent = 0;
                    if (isset($tag_parents[$type])) {
                        if (count($tags[$tag_parents[$type]]) > 0) {
                            $parent = $tags[$tag_parents[$type]][0];
                        }
                    }
                    $tags[$type] = tag_update('task', $new_task['id'], $type,
                        $values['tag_'.$type], $parent);
                }
            }
            mem_cache_delete("task-authors-by-id:".$new_task["id"]);

            flash("Task-ul a fost modificat cu succes.");
            redirect(url_task_edit($task_id));
        } else {
            flash_error("Sunt erori in datele introduse.");
        }
    }

    // Create view.
    $view = array();
    $view['title'] = $task_id;
    $view['page_name'] = url_task_edit($task_id);
    $view['task_id'] = $task_id;
    $view['task'] = $task;
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;
    $view['param_infos'] = $param_infos;

    execute_view_die("views/task_edit.php", $view);
}

// Creates a task. Minimalist
function controller_task_create() {
    global $identity_user;

    // Security check. FIXME: sort of a hack.
    identity_require_login();
    identity_require("task-create",
            task_init('new_task', 'classic', $identity_user));

    // Form stuff.
    $values = array();
    $errors = array();

    // Get form values
    $values['id'] = request('id', '');
    $values['type'] = request('type', 'classic');

    if (request_is_post()) {
        if (!is_task_id($values['id'])) {
            $errors['id'] = "Id de task invalid. Nu se permit majuscule!";
        } else if (task_get($values['id'])) {
            $errors['id'] = "Exista deja un task cu acest id";
        }
        if (!array_key_exists($values['type'], task_get_types())) {
            $errors['type'] = "Tip de task invalid";
        }

        if (!$errors) {
            $task = task_init(
                    $values['id'],
                    $values['type'],
                    $identity_user);
            $task_params = array();
            // FIXME: array_ magic?
            $param_infos = task_get_parameter_infos();
            foreach ($param_infos[$values['type']] as $name => $info) {
                $task_params[$name] = $info['default'];
            }

            // This should never fail.
            log_assert(task_create($task, $task_params, remote_ip_info()));
            flash("Un nou task a fost creeat, acum poti sa-l editezi");
            redirect(url_task_edit($task['id']));
        }
    }

    // Create view.
    $view = array();
    $view['title'] = "Creare task";
    $view['page_name'] = url_task_create();
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;

    execute_view_die("views/task_create.php", $view);
}

// Deletes a task.
function controller_task_delete($task_id) {
    if (!request_is_post()) {
        flash_error("Problema nu a putut fi stearsa.");
        redirect(url_home());
    }

    // Validate task_id
    if (!is_task_id($task_id)) {
        flash_error("Problema inexistenta.");
        redirect(url_home());
    }

    // Get task
    $task = task_get($task_id);
    if (!$task) {
        flash_error("Problema inexistenta.");
        redirect(url_home());
    }

    // Security check
    identity_require('task-delete', $task);

    // Delete the task
    task_delete($task);

    flash("Problema a fost stearsa.");
    redirect(url_home());
}

// Tag a task
function controller_task_tag($task_id) {
    if (!is_task_id($task_id)) {
        flash_error("Problema inexistenta");
        redirect(url_home());
    }

    $task = task_get($task_id);
    identity_require('task-tag', $task);

    if (!$task) {
        flash_error("Problema inexistenta");
        redirect(url_home());
    }

    if (request_is_post()) {
        $algorithm_tags_id = request("algorithm_tags");
        $method_tags_id = tag_get_parents($algorithm_tags_id);

        if (!is_array($algorithm_tags_id)) {
            flash_error("Datele trimise sunt invalide. Raporteaza aceasta problema unui admin.");
            redirect(url_task_edit_tag($task_id));
        }

        foreach ($algorithm_tags_id as $tag_id) {
            if (!is_tag_id($tag_id)) {
                flash_error("Datele trimise sunt invalide. Raporteaza aceasta problema unui admin.");
                redirect(url_task_edit_tag($task_id));
            }
        }

        $algorithm_tags = tag_get_by_ids($algorithm_tags_id);
        $count = 0;
        foreach ($algorithm_tags as $tag) {
            if ($tag['type'] == 'algorithm') {
                $count++;
            }
        }
        if ($count != count($algorithm_tags_id)) {
            flash_error("Datele trimise sunt invalide. Raporteaza aceasta problema unui admin.");
            redirect(url_task_edit_tag($task_id));
        }

        task_update_tags($task_id, $method_tags_id, $algorithm_tags_id);
        flash("Tagurile au fost salvate cu succes");
    }

    $tags_tree = tag_build_tree(tag_get_all(array("method", "algorithm")));

    // Get tags for task_id
    $task_tags = tag_get('task', $task_id, 'algorithm');

    $view['title'] = "Editeaza tagurile pentru problema ".$task['title'];
    $view['task'] = $task;
    $view['tags_tree'] = $tags_tree;
    $view['task_tags'] = $task_tags;
    execute_view_die('views/task_tag_edit.php', $view);
}

// Gets a list of tags from request
// Prints a list of tasks that contain all those tags
// Tasks must be in 'arhiva' or in 'arhiva educationala'
function controller_task_search() {
    $tags = request('tag_id', null);
    if (is_null($tags)) {
        $tags = array();
    }

    if (!is_array($tags)) {
        flash_error("Filtru invalid");
        redirect(url_home());
    }
    foreach ($tags as $tag) {
        if (!is_tag_id($tag)) {
            flash_error("Filtru invalid");
            redirect(url_home());
        }
    }

    if (identity_is_anonymous()) {
        $user_id = null;
    } else {
        $user_id = identity_get_user_id();
    }
    $tasks = task_filter_by_tags($tags, true, $user_id);
    foreach ($tasks as &$task) {
        $task['authors'] = task_get_authors($task['task_id']);
    }

    // Fetch the tags and all their parents so they can be displayed
    // in a tree-like fashion
    if (count($tags) > 0) {
        while ((
            $new_tags = array_unique(array_merge($tags, tag_get_parents($tags)))
        ) != $tags) {
            $tags = $new_tags;
        }
        $tags = tag_build_tree(tag_get_by_ids($tags));
    }

    $view['title'] = "Rezultatele filtrării";
    $view['tasks'] = $tasks;
    $view['tags'] = $tags;
    execute_view_die('views/task_filter_results.php', $view);
}

?>
