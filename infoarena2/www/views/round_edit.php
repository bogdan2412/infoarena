<?php

// link JS files for round editing
if (!isset($view['head'])) {
    $view['head'] = "";
}
$view['head'] .= "<)script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/dual.js") . "\"></script>";
$view['head'] .= "<)script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/roundedit.js") . "\"></script>";

require_once(IA_ROOT."common/round.php");
include('views/header.php');

?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= htmlentities(getattr($view, 'action')) ?>" method="post" class="task">
    <h2>Despre runda</h2>

    <ul class="form">
        <li id="field_active">
            <label for="form_active">Vizibilitate</label>
            <select name="active" id="form_active">
                <option value="0"<?= '0' == fval('active') ? ' selected="selected"' : '' ?>>Runda ascunsa</option>
                <option value="1"<?= '1' == fval('active') ? ' selected="selected"' : '' ?>>Runda vizibila (publica)</option>
            </select>
            <?= ferr_span('active')?>
        </li>

<?php
// FIXME: Field should be generated from round_get_types()
?>

        <li id="field_type">
            <label for="form_type">Tip runda</label>
            <select name="type" id="form_type">
                <option value=""<?= '' == fval('type') ? ' selected="selected"' : '' ?>>[ Alege ]</option>
                <option value="classic"<?= 'classic' == fval('type') ? ' selected="selected"' : '' ?>>Clasic</option>
            </select>
            <?= ferr_span('type') ?>
        </li>
    </ul>

    <h2>Task-uri</h2>

    <ul class="form">
        <li id="field_tasks">
            <label for="form_tasks">Alege task-urile acestei runde</label>
            <select name="tasks[]" id="form_tasks" multiple="multiple" size="10">
<?php foreach ($all_tasks as $task) { ?>
                <option value="<?= htmlentities($task['id']) ?>"<?= false !== array_search($task['id'], $form_values['tasks']) ? ' selected="selected"' : ''  ?>><?= htmlentities($task['title']) ?> [<?= htmlentities($task['id']) ?>]</option>
<?php } ?>
            </select>
            <?= ferr_span('tasks')?>
        </li>
    </ul>

    <h2>Parametri</h2>

    <? include('views/param_edit.php') ?>

    <div class="submit">
        <ul class="form">
            <li id="form_submit">
                <input type="submit" value="Salveaza" id="form_submit" class="button important" />
            </li>
        </ul>
    </div>
</form>

<?php include('footer.php'); ?>
