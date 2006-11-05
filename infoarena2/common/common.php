<?php

// Nicer way to get an element from an array. It returns a default value
// (defaulting to null) instead of throwing an error.
function getattr($dict, $attribute, $defaultValue = null) {
    if (isset($dict[$attribute])) {
        return $dict[$attribute];
    }
    else {
        return $defaultValue;
    }
}

function is_whole_number($x)
{
    return is_numeric($x) && $x == intval($x);
}

?>
