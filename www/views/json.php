<?php

log_assert($view['json']);
$output = json_encode($view['json']);

header("Content-Type: application/json\n\n");
echo $output;
