<?php

// Various string processing functions.

function starts_with($s, $substr) {
    log_assert(is_string($s) && is_string($substr));
    return substr($s, 0, strlen($substr)) == $substr;
}

function ends_with($s, $substr) {
    log_assert(is_string($s) && is_string($substr));
    return substr($s, -strlen($substr)) == $substr;
}

?>
