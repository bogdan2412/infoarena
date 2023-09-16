<?php

require_once(Config::ROOT . "common/job.php");

// Big bad submit controller.
function controller_submit() {
  Identity::enforceLoggedIn();

  $view = array(
    'title' => 'Trimite soluție',
  );

  $values = array();
  $errors = array();

  if (Request::isPost()) {
    $values = [
      'task_id' => request('task_id'),
      'compiler_id' => request('compiler_id'),
      'round_id' => request('round_id'),
      'remote_ip_info' => remote_ip_info(),
    ];

    // Check uploaded solution
    if (isset($_FILES['solution']) && is_uploaded_file($_FILES['solution']['tmp_name'])) {
      $values['solution'] = file_get_contents($_FILES['solution']['tmp_name']);
    }

    $errors = safe_job_submit($values);

    // The end.
    if (!isset($errors["round_id"])) {
      $_SESSION["_ia_last_submit_round"] = $values["round_id"];
    }

    if (isset($errors['submit_limit']) && count($errors) == 1) {
      FlashMessage::addError($errors['submit_limit']);
      Util::redirectToReferrer();
    }

    if ($errors) {
      FlashMessage::addError('NU am salvat soluția trimisă. Unul sau mai multe câmpuri
                         nu au fost completate corect.');
    } else {
      FlashMessage::addSuccess('Am salvat soluția.');
      Util::redirectToReferrer();
    }
    // Fall through to submit form.
  }

  // get task list.
  // FIXME: proper filter?
  $tasks = Model::factory('Task')->find_many();
  $filteredTasks = [];
  foreach ($tasks as $t) {
    if ($t->canSubmit()) {
      $filteredTasks[$t->id] = $t->as_array();
    }
  }

  $view['tasks'] = $filteredTasks;
  $view['form_errors'] = $errors;
  $view['form_values'] = $values;

  execute_view_die('views/submit.php', $view);
}
