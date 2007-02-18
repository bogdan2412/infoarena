<?php

require_once(IA_ROOT_DIR.'www/JSON.php');

// encode JSON
$json = new Services_JSON();
assert($view['json']);
$output = $json->encode($view['json']);

// serve JSON
if ($debug) {
    header("Content-Type: text/plain\n\n");
    echo $output;
}
else {
    header("Content-Type: application/json\n\n");
    echo $output;
}

?>
