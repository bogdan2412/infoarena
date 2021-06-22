<?php

include(CUSTOM_THEME.'header.php');

// list of task ids that require output-only submissions
$output_only_ids = array();
foreach ($tasks as $t) {
    if ('output-only' != $t['type']) {
        continue;
    }
    $output_only_ids[] = $t['id'];
}

?>

<h1><?= html_escape($title)  ?></h1>

<div id="sidebar2">
<div class="section">
<h3> Ce se întamplă cu sursa mea? </h3>
<ul>
    <li>Sursa ta se evaluează <a href="/documentatie/evaluator">automat</a>, iar rezultatul evaluării se poate vedea în <a href="/monitor">monitor</a></li>
</ul>
</div>
</div>

<?php
require_once("submit_form.php");
display_submit_form(false, fval("task_id", false));
?>

<?php wiki_include('template/trimite-solutii') ?>

<?php include('footer.php'); ?>
