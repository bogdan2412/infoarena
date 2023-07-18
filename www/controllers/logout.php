<?php

function controller_logout() {
  Identity::enforceLoggedIn();

  if (!request_is_post()) {
    FlashMessage::addError('Nu te-am putut deconecta.');
    redirect(url_home());
  }

  Session::logout();
}

?>
