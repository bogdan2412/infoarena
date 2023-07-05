<?php

// link JS files for round editing
if (!isset($view['head'])) {
    $view['head'] = "";
}
$view['head'] .= "<script src=\"" . html_escape(url_static("js/dual.js")) . "\" ></script>";
$view['head'] .= "<script src=\"" . html_escape(url_static("js/roundedit.js")) . "\" ></script>";
$view['head'] .= "<script src=\"" . html_escape(url_static("js/parameditor.js")) . "\" ></script>";

require_once(IA_ROOT_DIR."common/round.php");
require_once(IA_ROOT_DIR."www/format/form.php");
require_once(IA_ROOT_DIR."www/views/round_edit_header.php");
require_once 'header.php';

echo round_edit_tabs($view['round_id'], 'round-edit-params');

// Validate $view values.
log_assert(is_array($all_tasks));
foreach ($all_tasks as $task) {
    log_assert_valid(task_validate($task));
}
log_assert(is_array($form_values['tasks']));
foreach ($form_values['tasks'] as $tid) {
    log_assert(is_task_id($tid));
}

$tasks_field_values = array();
foreach ($all_tasks as $task) {
    $tasks_field_values[$task['id']] = "{$task['title']} [{$task['id']}]";
}

// Init form field definitions.
$form_fields = array(
        'title' => array(
                'name' => 'Titlu',
                'default' => $round['id'],
                'type' => 'string',
        ),
        'page_name' => array(
                'name' => "Pagina de prezentare",
                'description' => "Aceasta este pagina la care este trimis utilizatorul ".
                                 "când dă click pe o rundă.",
                'type' => 'string',
        ),
        'start_time' => array(
                'name' => "Timpul de start",
                'description' => "Timpul trebuie să fie UTC în format YYYY-MM-DD HH:MM:SS",
                'type' => 'datetime',
        ),
        'tasks' => array(
                'name' => "Lista de probleme",
                'type' => 'set',
                'values' => $tasks_field_values,
        ),
        'type' => array(
                'name' => 'Tipul rundei',
                'type' => 'enum',
                'values' => $round_types,
                'default' => 'user-defined',
        ),
        'public_eval' => array(
                'name' => 'Evaluare publică',
                'description' => "Concurenții pot vedea scorul obținut la sursele trimise.",
                'default' => '0',
                'type' => 'bool',
        ),
);
?>

<h1>Editare runda <?= format_link(url_textblock($round['page_name']), $round['title']) ?></h1>

<form action="<?= html_escape(url_round_delete($round['id'])) ?>" method="post" style="float: right">
    <input type="hidden" name="" value="<?= html_escape($round['id']) ?>">
    <input onclick="" type="submit" value="Șterge runda" id="form_delete" class="button important">
</form>

<form action="<?= html_escape(getattr($view, 'action')) ?>" method="post" class="task">
 <fieldset>
  <legend>Informații generale</legend>
  <ul class="form">
   <?= view_form_field_li($form_fields['title'], 'title') ?>
   <?= view_form_field_li($form_fields['page_name'], 'page_name') ?>
   <?= view_form_field_li($form_fields['start_time'], 'start_time') ?>
   <?= view_form_field_li($form_fields['tasks'], 'tasks') ?>
  </ul>
 </fieldset>
 <fieldset>
  <legend>Parametri</legend>
  <ul class="form">
   <?= view_form_field_li($form_fields['type'], 'type') ?>
   <?= view_form_field_li($form_fields['public_eval'], 'public_eval') ?>
   <li><hr></li>
   <li id="field_params">
    <?= format_param_editor_list(
        $param_infos, $form_values, $form_errors); ?>
   </li>
  </ul>
 </fieldset>
 <div class="submit">
  <ul class="form">
   <li id="field_submit">
    <input type="submit"
           value="Salvează"
           id="form_submit"
           class="button important">
   </li>
  </ul>
 </div>
</form>

<?php include('footer.php'); ?>
