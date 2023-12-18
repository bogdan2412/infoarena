<?php
require_once Config::ROOT . 'common/db/user.php';
require_once Config::ROOT . 'common/user.php';
require_once Config::ROOT . 'www/views/utilities.php';

function controller_penalty() {

    // `data` dictionary is a dictionary with data to be displayed by form view
    $data = array();

    // here we store validation errors.
    // It is a dictionary, indexed by field names
    $errors = array();

    if (!Identity::isAdmin()) {
        Util::redirectToHome();
    }

    // submit?
    $submit = Request::isPost();

    if ($submit) {
        // 1. validate

        $data['username'] = getattr($_POST, 'username');
        // check username
        if ($data['username']) {
            $user = user_get_by_username($data['username']);
            if (!$user) {
                $errors['username'] = 'Nu există niciun utilizator cu acest ' .
                    'nume de cont.';
            }
        } else {
          $errors['username'] = 'Trebuie să completezi acest cîmp.';
        }

        $data['round_id'] = getattr($_POST, 'round_id');
        // check contest_id
        if ($data['round_id']) {
            $round = round_get($data['round_id']);
            if (!$round) {
                $errors['round_id'] = 'Nu există runda cu acest ID.';
            }
        } else {
          $errors['round_id'] = 'Trebuie să completezi acest cîmp.';
        }

        if (isset($user) && $user && isset($round) && $round) {
            redirect(url_penalty_edit($user['id'], $round['id']));
        }

    }
    else {
        // initial display of form
    }

    RecentPage::addCurrentPage('Penalty');
    Smart::assign([
      'formErrors' => $errors,
      'formValues' => $data,
    ]);
    Smart::display('penalty.tpl');
}
