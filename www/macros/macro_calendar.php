<?php

require_once(IA_ROOT_DIR.'www/macros/macro_remotebox.php');

function macro_calendar() {
    $args = array(
        'url' => IA_SMF_URL.'/ia_calendar.php'
    );
    return macro_remotebox($args, true);
}

?>
