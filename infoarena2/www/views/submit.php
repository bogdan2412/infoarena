<?php

include('header.php');

// list of task ids that require output-only submissions
$output_only_ids = array();
foreach ($tasks as $t) {
    if ('output-only' != $t['type']) {
        continue;
    }
    $output_only_ids[] = $t['id'];
}

?>

<h1><?= htmlentities($title)  ?></h1>

<div id="sidebar2">
<div class="section">
<h3> Ce se intampla cu sursa mea? </h3>
<ul>
    <li>Sursa ta se evalueaza <a href="/documentatie/evaluator">automat</a>, iar rezultatul evaluarii se poate vedea in <a href="/monitor">monitor</a></li>
</ul>
</div>
</div>

<form enctype="multipart/form-data" action="<?= htmlentities(url_submit()) ?>" method="post" class="submit" id="task_submit">

<input type="hidden" id="output_only" value="<?= htmlentities(':'.join(':', $output_only_ids).':') ?>" />

<ul class="form">
    <li id="field_task">
        <label for="form_task">Problema</label>
        <select name="task_id" id="form_task">
            <option value="">[ Alegeti problema ]</option>
<?php foreach ($tasks as $task) {  ?>
            <option value="<?= htmlentities($task['id']) ?>"<?= fval('task_id') == $task['id'] ? ' selected="selected"' : '' ?>><?= htmlentities($task['title']) ?></option>
<?php } ?>
        </select>
        <?= ferr_span('task_id') ?>
    </li>

    <li id="field_solution">
        <label for="form_solution">Fisier solutie</label>
        <input type="file" name="solution" id="form_solution" />
        <?= ferr_span('solution', false) ?>
    </li>

    <li id="field_compiler">
        <label for="form_compiler">Compilator</label>
        <select name="compiler_id" id="form_compiler">
            <option value="-">[ Alegeti compilator ]</option>
            <option value="c"<?= 'c' == fval('compiler_id') ? ' selected="selected"' : '' ?>>GNU C</option>
            <option value="cpp"<?= 'cpp' == fval('compiler_id') ? ' selected="selected"' : '' ?>>GNU C++</option>
            <option value="fpc"<?= 'fpc' == fval('compiler_id') ? ' selected="selected"' : '' ?>>FreePascal</option>
        </select>
        <?= ferr_span('compiler_id') ?>
    </li>

    <li id="field_submit">
        <input type="submit" class="button important" value="Trimite solutia" id="form_submit" />
    </li>
</ul>
</form>

<?php wiki_include('template/trimite-solutii') ?>

<?php include('footer.php'); ?>
