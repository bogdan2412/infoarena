<?php

function controller_logout() {
    if (!request_is_post()) {
        flash_error("Sesiunea nu a putut fi inchisa!");
        redirect(url_home());
    }
    identity_end_session();

    flash('Sesiunea a fost inchisa!');
    redirect(url_home());
}

?>
