<?php

$view['head'] = '<script type="text/javascript" src="' . url('static/js/submit.js') . '"></script>';

include('header.php');

$output_only_ids = array();
foreach ($tasks as $t) {
    if ('output-only' != $t['type']) {
        continue;
    }
    $output_only_ids[] = $t['id'];
}

?>

<h1><?= htmlentities($title)  ?></h1>

<div class="sidehelp">
    Daca intampinati probleme la trimiterea solutiilor puteti sa consultati
    <a href="<?= url('Ajutor/Trimite_Solutii') ?>">documentatia</a>.
</div>

<form enctype="multipart/form-data" action="<?= url('submit', array('action' => 'save')) ?>" method="post" class="submit">

<input type="hidden" id="output_only" value="<?= ':' . join(':', $output_only_ids) . ':' ?>" />

<ul class="form">
    <li id="field_round">
        <label for="form_round">Runda</label>
        <select name="round_id" id="form_round">
            <option value="">[ Alegeti runda ]</option>
<?php foreach ($rounds as $round) {  ?>
            <option value="<?= htmlentities($round['id']) ?>"<?= fval('round_id') == $round['id'] ? ' selected="selected"' : '' ?>><?= htmlentities($round['title']) ?></option>
<?php } ?>
        </select>
        <?= ferr_span('round_id') ?>
    </li>

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
            <option value="-">[ Altceva ]</option>
            <option value="c"<?= 'c' == fval('compiler_id') ? ' selected="selected"' : '' ?>>GNU C</option>
            <option value="cpp"<?= 'c' == fval('compiler_id') ? ' selected="selected"' : '' ?>>GNU C++</option>
            <option value="fpc"<?= 'c' == fval('compiler_id') ? ' selected="selected"' : '' ?>>FreePascal</option>
        </select>
        <?= ferr_span('compiler_id') ?>
        <span class="fieldHelp"><a href="<?= url('Compilatoare') ?>">Detalii despre compilatoare</a></span>
    </li>

    <li id="field_submit">
        <input type="submit" class="button important" value="Trimite solutia" id="form_submit" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
