<?php

function macro_debug($args) {
    if (!identity_can('macro-debug')) {
        return macro_permission_error();
    }

    $res = "<p>Debug macro listing args</p>";
    $res .= '<pre>';
    $ncargs = $args;
    $res .= html_escape(print_r($ncargs, true));
    $res .= '</pre>';

    return $res;
}

?>
