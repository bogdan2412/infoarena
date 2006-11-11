<?php

// This is called from textile like
// ==Hello(key1 = value1 key2 = value2)==
// There is an extra global argument called page_name.
function macro_hello($args) {
    $target = getattr($args, 'target', 'anonim');
    return '<strong>Hello, '.$target.'</strong>';
}

?>
