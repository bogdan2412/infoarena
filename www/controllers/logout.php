<?php

function controller_logout() {
    if (!request_is_post()) {
        FlashMessage::addError('Nu te-am putut deconecta.');
        redirect(url_home());
    }
    identity_end_session();

    FlashMessage::addSuccess('Pe curînd!');
    redirect(url_home());
}

?>
