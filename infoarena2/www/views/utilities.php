<?php

function fval($paramName) {
    global $data;
    return htmlentities(getattr($data, $paramName));
}

?>
