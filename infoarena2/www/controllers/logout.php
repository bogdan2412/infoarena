<?php

// check permissions
identity_require('logout');

identity_end_session();

flash('Sesiunea a fost inchisa!');
redirect(url(''));

?>
