<?php
include('views/wikiedit_parts.php'); 

// link JS files for round editing
$view['head'] .= "<script type=\"text/javascript\" src=\"" . url("static/js/dual.js") . "\"></script>";
$view['head'] .= "<script type=\"text/javascript\" src=\"" . url("static/js/roundedit.js") . "\"></script>";

// include header
include('views/header.php'); 

?>

<h1><?= getattr($view, 'title') ?></h1>

<?= $wikiedit['preview'] ?>

<form action="<?= getattr($view, 'action') ?>" method="post" class="round">
<div class="tabber">
    <div class="tabbertab<?= 'statement' == $active_tab ? ' tabbertabdefault' : '' ?> statement">
        <h3>Pagina runda</h3>
        <ul class="form">
            <?= $wikiedit['title'] ?>
            
            <?= $wikiedit['content'] ?>
        </ul>
    </div>

    <div class="tabbertab<?= 'tasks' == $active_tab ? ' tabbertabdefault' : '' ?> parameters">
        <h2>Task-uri</h2>

<ul class="form">
    <li id="field_tasks">
        <label for="form_tasks">Alege task-urile acestei runde</label>
            <select name="tasks[]" id="form_tasks" multiple="multiple" size="10">
<?php foreach ($all_tasks as $task) { ?>
    <option value="<?= $task['id'] ?>"<?= false !== array_search($task['id'], $form_values['tasks']) ? ' selected="selected"' : ''  ?>><?= $task['title'] ?> [<?= $task['id'] ?>]</option>
<?php } ?>
            </select>
            <?= ferr_span('tasks')?>
    </li>
</ul>
    </div>

    <div class="tabbertab<?= 'parameters' == $active_tab ? ' tabbertabdefault' : '' ?> parameters">
        <h2>Parametri</h2>

<ul class="form">
    <li id="field_type">
        <label for="form_type">Tip runda</label>
        <select name="type" id="form_type">
            <option value=""<?= '' == fval('type') ? ' selected="selected"' : '' ?>>[ Alege ]</option>
            <option value="classic"<?= 'classic' == fval('type') ? ' selected="selected"' : '' ?>>Clasic</option>
            <option value="debug"<?= 'debug' == fval('type') ? ' selected="selected"' : '' ?>>Debug</option>
            <option value="speed"<?= 'speed' == fval('type') ? ' selected="selected"' : '' ?>>Speed</option>
        </select>
        <?= ferr_span('type') ?>
    </li>
</ul>

<? include('views/param_edit.php') ?>

    </div>

</div>

<div class="submit">
    <ul class="form">
        <?= $wikiedit['submit'] ?>
    </ul>
</div>

</form>
<?php include('footer.php'); ?>
