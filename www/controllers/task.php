<?php

require_once(Config::ROOT . "common/db/task.php");
require_once(Config::ROOT . "common/task.php");
require_once(Config::ROOT . "common/tags.php");
require_once(Config::ROOT . "common/task_rating.php");

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
    FlashMessage::addError('Identificatorul de task este invalid.');
    Util::redirectToHome();
  }

  // Get task
  $task = Task::get_by_id($task_id);
  if (!$task) {
    FlashMessage::addError("Problema nu există.");
    Util::redirectToHome();
  }

  Identity::enforceEditTask($task);

  // get parameter list for tasks (in general, not for this specific task)
  $param_infos = task_get_parameter_infos();
  $task_params = task_get_parameters($task->id);

  // Form stuff.
  $values = array();
  $errors = array();

  // Fill in form values from request, defaults in $task
  $fields = array('type', 'source', 'security', 'title', 'page_name',
                  'open_source', 'open_tests', 'test_count', 'test_groups',
                  'evaluator', 'use_ok_files', 'public_tests');

  foreach ($fields as $field) {
    $values[$field] = request($field, $task->$field);
  }

  // Parameter values, for all possible types of tasks.
  // Yucky, but functional.
  foreach (task_get_types() as $task_type => $pretty_name) {
    foreach ($param_infos[$task_type] as $name => $info) {
      $form_name = "param_{$task_type}_{$name}";
      $def = $info['default'];
      if ($task_type == $task->type) {
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
  if ($task->isAuthorEditable()) {
    if ($task->user_id) {
      $user = user_get_by_id($task->user_id);
      log_assert($user, "Task has invalid user_id");
      $username = $user["username"];
    } else {
      $username = "";
    }
    $values["user"] = request("user", $username);
  }

  // Validate the monkey.
  if (Request::isPost()) {
    // Build new task
    $new_task = $task->parisClone();
    foreach ($fields as $field) {
      $new_task->$field = $values[$field];
    }

    if ($task->isAuthorEditable()) {
      if ($values["user"] == "") {
        $new_task->user_id = 0;
      } else {
        $user = user_get_by_username($values["user"]);
        if (!$user) {
          $errors["user"] = "Utilizator inexistent.";
        } else {
          $new_task->user_id = $user["id"];
        }
      }
    }

    $task_errors = task_validate($new_task->as_array());
    $errors = $task_errors;

    // Check security.
    if ($new_task->security != $task->security) {
      Identity::enforceEditTaskSecurity($new_task);
    }

    // Handle task parameters. Only for current type, and only if
    // properly selected.
    $new_task_params = $task_params;
    if (!array_key_exists('type', $task_errors)) {
      $task_type = $new_task->type;
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
      task_update($new_task->as_array());
      task_update_parameters($task_id, $new_task_params);

      if ($new_task->areTagsEditable()) {
        foreach ($tag_types as $type) {
          $parent = 0;
          if (isset($tag_parents[$type])) {
            if (count($tags[$tag_parents[$type]]) > 0) {
              $parent = $tags[$tag_parents[$type]][0];
            }
          }
          $tags[$type] = tag_update('task', $new_task->id, $type,
                                    $values['tag_'.$type], $parent);
        }
      }

      FlashMessage::addSuccess("Am salvat parametrii.");
      redirect(url_task_edit($task_id, 'task-edit-params'));
    } else {
      FlashMessage::addError("Sunt erori în datele introduse.");
    }
  }

  // Create view.
  $view = array();
  $view['title'] = 'Editează parametrii pentru problema '. $task->title;
  $view['page_name'] = url_task_edit($task_id);
  $view['task_id'] = $task_id;
  $view['task'] = $task;
  $view['form_values'] = $values;
  $view['form_errors'] = $errors;
  $view['param_infos'] = $param_infos;

  // Secure deletion requires the user to delete all the attachments first
  // in order to prevent accidental deletion of valuable data. Secure
  // deletion kicks in when (1) the task has attachments and (2) secure
  // deletion is enabled in the config file.
  $view['secure_delete'] =
    Config::SECURE_DELETION &&
    count(attachment_get_all($task->page_name));

  execute_view_die("views/task_edit.php", $view);
}

// Creates a task. Minimalist
function controller_task_create() {
  Identity::enforceCreateTask();

  // Form stuff.
  $values = array();
  $errors = array();

  // Get form values
  $values['id'] = request('id', '');
  $values['type'] = request('type', 'classic');

  if (Request::isPost()) {
    if (!is_task_id($values['id'])) {
      $errors['id'] = "ID de task invalid. Nu se permit majuscule!";
    } else if (task_get($values['id'])) {
      $errors['id'] = "Există deja un task cu acest ID.";
    }
    if (!array_key_exists($values['type'], task_get_types())) {
      $errors['type'] = "Tip de task invalid.";
    }

    if (!$errors) {
      $task = task_init(
        $values['id'],
        $values['type'],
        Identity::get());
      $task_params = array();
      // FIXME: array_ magic?
      $param_infos = task_get_parameter_infos();
      foreach ($param_infos[$values['type']] as $name => $info) {
        $task_params[$name] = $info['default'];
      }

      // This should never fail.
      log_assert(task_create($task, $task_params, remote_ip_info()));
      FlashMessage::addSuccess("Am creat problema, acum poți să o editezi.");
      redirect(url_task_edit($task['id'], 'task-edit-params'));
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
  if (!Request::isPost()) {
    FlashMessage::addError("Problema nu a putut fi ștearsă.");
    Util::redirectToHome();
  }

  // Validate task_id
  if (!is_task_id($task_id)) {
    FlashMessage::addError("Problemă inexistentă.");
    Util::redirectToHome();
  }

  // Get task
  $task = Task::get_by_id($task_id);
  if (!$task) {
    FlashMessage::addError("Problemă inexistentă.");
    Util::redirectToHome();
  }

  // Security check
  Identity::enforceDeleteTask($task);

  // Delete the task
  task_delete($task->as_array());

  FlashMessage::addSuccess("Am șters problema.");
  Util::redirectToHome();
}

// Edit ratings for a task
function controller_task_ratings($task_id) {
  // Validate task id
  if (!is_task_id($task_id)) {
    FlashMessage::addError("Problemă inexistentă.");
    Util::redirectToHome();
  }

  // Get task
  $task = Task::get_by_id($task_id);
  if (!$task) {
    falsh_error("Problemă inexistentă.");
    Util::redirectToHome();
  }

  Identity::enforceEditTaskRatings($task);
  $user_id = Identity::getId();

  // Form stuff
  $ratings = array();
  $errors = array();

  if (Request::isPost()) {
    $rating_fields = array('idea', 'theory', 'coding');

    foreach ($rating_fields as $rating_field) {
      $rating_value = request($rating_field);

      if (!task_is_rating_value($rating_value)) {
        FlashMessage::addError("Datele introduse nu sunt valide.");
        redirect(url_task_edit($task_id, 'task-edit-ratings'));
      }

      $ratings[$rating_field] = $rating_value;
    }

    task_rating_add($task_id, $user_id, $ratings);

    FlashMessage::addSuccess("Am salvat ratingurile.");
    redirect(url_task_edit($task_id, 'task-edit-ratings'));
  }

  $ratings = task_rating_get($task_id, $user_id);

  $view = array();
  $view['title'] = "Editează ratingurile pentru problema " . $task->title;
  $view['task_id'] = $task_id;
  $view['form_values'] = $ratings;
  $view['form_errors'] = $errors;

  execute_view_die('views/task_rating_edit.php', $view);
}

// Tag a task
function controller_task_tag($task_id) {
  if (!is_task_id($task_id)) {
    FlashMessage::addError("Problemă inexistentă.");
    Util::redirectToHome();
  }

  $task = Task::get_by_id($task_id);
  if (!$task) {
    FlashMessage::addError("Problemă inexistentă.");
    Util::redirectToHome();
  }

  Identity::enforceEditTaskTags($task);

  if (Request::isPost()) {
    $algorithm_tags_id = request("algorithm_tags", array());
    $method_tags_id = tag_get_parents($algorithm_tags_id);
    $reportAdminMsg = 'Datele trimise sunt invalide. Raportează această problemă unui admin.';

    if (!is_array($algorithm_tags_id)) {
      FlashMessage::addError($reportAdminMsg);
      redirect(url_task_edit($task_id, 'task-edit-tags'));
    }

    foreach ($algorithm_tags_id as $tag_id) {
      if (!is_tag_id($tag_id)) {
        FlashMessage::addError($reportAdminMsg);
        redirect(url_task_edit($task_id, 'task-edit-tags'));
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
      FlashMessage::addError($reportAdminMsg);
      redirect(url_task_edit($task_id, 'task-edit-tags'));
    }

    task_update_tags($task_id, $method_tags_id, $algorithm_tags_id);
    FlashMessage::addSuccess("Am salvat tagurile.");
    redirect(url_task_edit($task_id, 'task-edit-tags'));
  }

  $tags_tree = tag_build_tree(tag_get_all(array("method", "algorithm")));

  // Get tags for task_id
  $task_tags = tag_get('task', $task_id, 'algorithm');

  $view = array();
  $view['title'] = "Editează tagurile pentru problema " . $task->title;
  $view['task'] = $task;
  $view['tags_tree'] = $tags_tree;
  $view['task_tags'] = $task_tags;
  execute_view_die('views/task_tag_edit.php', $view);
}

// Gets a list of tags from request
// Prints a list of tasks that contain all those tags
// Tasks security must be public
function controller_task_search() {
  $tags = request('tag_id', null);
  if (is_null($tags)) {
    $tags = array();
  }

  if (!is_array($tags)) {
    FlashMessage::addError("Filtru invalid.");
    Util::redirectToHome();
  }
  foreach ($tags as $tag) {
    if (!is_tag_id($tag)) {
      FlashMessage::addError("Filtru invalid.");
      Util::redirectToHome();
    }
  }

  $user_id = Identity::getId();
  $tasks = task_filter_by_tags($tags, true, $user_id);
  foreach ($tasks as &$task) {
    $task['authors'] = task_get_authors($task['task_id']);
  }

  // Fetch the tags and all their parents so they can be displayed
  // in a tree-like fashion
  $selected_tags = array();
  if (count($tags) > 0) {
    $selected_tags = $tags;
    while ((
      $new_tags = array_unique(array_merge($tags,
                                           tag_get_parents($tags)))
    ) != $tags) {
      $tags = $new_tags;
    }
  }

  $authors = tag_get_with_counts(array('author'), $tags);
  $tags = tag_build_tree(tag_get_with_counts(array('method', 'algorithm'),
                                             $tags));
  $view = array();
  $view['title'] = "Rezultatele filtrării";
  $view['tasks'] = $tasks;
  $view['tags'] = $tags;
  $view['selected_tags'] = $selected_tags;
  $view['authors'] = $authors;
  execute_view_die('views/task_filter_results.php', $view);
}
