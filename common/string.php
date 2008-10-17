<?php

  // Various string processing functions.

function starts_with($s, $substr) {
    return substr($s, 0, strlen($substr)) == $substr;
}

function ends_with($s, $substr) {
    return substr($s, -strlen($substr)) == $substr;
}

?>
