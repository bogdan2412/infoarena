<?php

require_once('JSON.php');

// encode JSON
$json = new Services_JSON();
assert($view['json']);
$output = $json->encode($view['json']);

// serve JSON
if ($view['debug']) {
    header("Content-Type: text/plain\n\n");
    echo $output;
}
else {
    header("Content-Type: application/json\n\n");
    echo $output;
}

// make sure it all ends here
die();

?>
