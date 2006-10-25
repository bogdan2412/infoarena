<?php

function controller_logout() {
    // check permissions
    identity_require('user-logout');

    identity_end_session();

    flash('Sesiunea a fost inchisa!');
    redirect(url(''));
}

?>
