<?php

require_once(Config::ROOT . "common/db/user.php");

function controller_login() {
  if (Identity::isLoggedIn()) {
    Util::redirectToHome();
  }

  $username = Request::get('username');
  $password = Request::get('password');
  $remember = Request::has('remember');
  $referrer = Util::getReferrer();

  if (request::isPost()) {
    $user = User::getByUsernamePlainPassword($username, $password);

    if ($user) {
      Session::login($user, $remember, $referrer);
    } else {
      FlashMessage::addError(
        'Nume de utilizator inexistent sau parolă incorectă. Încearcă din nou.');
    }
  }

  Smart::assign([
    'referrer' => $referrer,
    'remember' => $remember,
    'showSidebarLogin' => false,
    'username' => $username,
  ]);
  Smart::display('auth/login.tpl');
}
