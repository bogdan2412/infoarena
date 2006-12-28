<?php

// link JS files for round editing
if (!isset($view['head'])) {
    $view['head'] = "";
}
$view['head'] .= "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/dual.js")) . "\" ></script>";
$view['head'] .= "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/roundedit.js")) . "\" ></script>";
$view['head'] .= "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/paramedit.js")) . "\" ></script>";

require_once(IA_ROOT."common/round.php");
include('views/header.php');

// Validate $view values.
log_assert(is_array($all_tasks));
foreach ($all_tasks as $task) {
    log_assert_valid(task_validate($task));
}
log_assert(is_array($form_values['tasks']));
foreach ($form_values['tasks'] as $tid) {
    log_assert(is_task_id($tid));
}

?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= htmlentities(getattr($view, 'action')) ?>" method="post" class="task">
    <ul class="form">
        <li id="field_title">
            <?= format_form_text_field('title', 'Titlu') ?>
        </li>

        <li id="field_page_name">
            <?= format_form_text_field('page_name', 'Pagina de prezentare') ?>
        </li>
 
        <li id="field_type">
            <label for="form_type">Tipul rundei</label>
                <select name="type" id="form_type">
                    <option value=""<?= '' == fval('type') ? ' selected="selected"' : '' ?>>[ Alege ]</option>
                    <option value="classic"<?= 'classic' == fval('type') ? ' selected="selected"' : '' ?>>Clasic</option>
                </select>
            <?= ferr_span('type')?>
        </li>

        <li id="field_params">
            <label>Parametri</label>
            <? include('views/param_edit.php') ?>
        </li>

        <li id="field_tasks">
            <label for="form_tasks">Alege task-urile acestei runde</label>
            <select name="tasks[]" id="form_tasks" multiple="multiple" size="10">
<?php
// Show an option tag for every task
foreach ($all_tasks as $task) { 
    $attribs = array();
    $attribs['value'] = $task['id'];
    if (array_search($task['id'], $form_values['tasks']) !== false) {
        $attribs['selected'] = 'selected';
    }
    $content = "{$task['title']} [{$task['id']}]";
    echo format_tag('option', $content, $attribs);
}
?>
            </select>
            <?= ferr_span('tasks')?>
        </li>
    </ul>

    <div class="submit">
        <ul class="form">
            <li id="field_submit">
                <input type="submit" value="Salveaza" id="form_submit" class="button important" />
            </li>
        </ul>
    </div>
</form>

<?php include('footer.php'); ?>
