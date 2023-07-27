<?php

function controller_logout() {
  Identity::enforceLoggedIn();

  if (!request_is_post()) {
    FlashMessage::addError('Nu te-am putut deconecta.');
    Util::redirectToHome();
  }

  Session::logout();
}

?>
