<?php

// link JS files for round editing
if (!isset($view['head'])) {
    $view['head'] = "";
}
$view['head'] .= "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/dual.js")) . "\" ></script>";
$view['head'] .= "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/roundedit.js")) . "\" ></script>";
$view['head'] .= "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/parameditor.js")) . "\" ></script>";

require_once(IA_ROOT_DIR."common/round.php");
require_once(IA_ROOT_DIR."www/format/form.php");
include('views/header.php');
include('views/tags_header.php');

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
                                 "cand da click pe o runda",
                'type' => 'string',
        ),
        'start_time' => array(
                'name' => "Timpul de start",
                'description' => "Timpul trebuie sa fie UTC in format YYYY-MM-DD HH:MM:SS",
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
                'values' => round_get_types(),
                'default' => 'classic',
        ),
);
?>

<h1>Editare runda <?= format_link(url_textblock($round['page_name']), $round['title']) ?></h1>

<?php if ($round['state'] == 'running') { ?>
    <div class="warning">
     Atentie! Runda este activa chiar acum. Orice modificare poate avea urmari neplacute.
    </div>
<?php } elseif ($round['state'] == 'waiting') { ?>
    Aceasta runda nu a rulat inca.
<?php } elseif ($round['state'] == 'complete') { ?>
    <div class="warning">
     Atentie! Aceasta runda s-a terminat, orice modificare este descurajata.
    </div>
<?php } ?>

<form action="<?= htmlentities(getattr($view, 'action')) ?>" method="post" class="task" <?= tag_form_event() ?>>
 <fieldset>
  <legend>Informatii generale</legend>
  <ul class="form">
   <?= view_form_field_li($form_fields['title'], 'title') ?>
   <?= view_form_field_li($form_fields['page_name'], 'page_name') ?>
   <?= view_form_field_li($form_fields['start_time'], 'start_time') ?>
   <? if (identity_can('round-tag', $round)) { ?>
      <?= tag_format_input_box(fval('tags')) ?>
   <? } ?>
   <?= view_form_field_li($form_fields['tasks'], 'tasks') ?>
  </ul>
 </fieldset>
 <fieldset>
  <legend>Parametri</legend>
  <ul class="form">
   <?= view_form_field_li($form_fields['type'], 'type') ?>
   <li><hr /></li>
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
           value="Salveaza"
           id="form_submit"
           class="button important" />
   </li>
  </ul>
 </div>
</form>

<?php include('footer.php'); ?>
