<?php

function controller_logout() {
  Identity::enforceLoggedIn();

  if (!Request::isPost()) {
    FlashMessage::addError('Nu te-am putut deconecta.');
    Util::redirectToHome();
  }

  Session::logout();
}

?>
