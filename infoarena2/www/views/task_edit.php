<?php
require_once(IA_ROOT_DIR."common/task.php");
require_once(IA_ROOT_DIR."www/format/form.php");

$view['head'] = getattr($view, 'head').
    "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/parameditor.js")) . "\"></script>";

include('views/header.php');

// Validate task.
log_assert_valid(task_validate($task));

$form_fields = array(
        'title' => array(
                'name' => "Titlul problemei",
                'description' => "Nume sub care apare problema pentru utilizator",
                'type' => 'string',
        ),
        'page_name' => array(
                'name' => "Pagina cu enuntul",
                'description' => "Aceasta este pagina la care este trimis utilizatorul ".
                                 "cand da click pe o problema",
                'type' => 'string',
        ),
        'author' => array(
                'name' => "Autor(i)",
                'type' => 'string',
        ),
        'source' => array(
                'name' => "Sursa",
                'type' => 'string',
        ),
        'hidden' => array(
                'name' => 'Vizibilitate',
                'description' => 'Daca problema este vizibila pentru utilizatorii '.
                                 'de rand. Cand o runda incepe probleme devin automat '.
                                 'vizibile',
                'type' => 'enum',
                'values' => array(
                        '1' => 'Task ascuns',
                        '0' => 'Task vizibil',
                ),
                'default' => '1',
        ),
        'type' => array(
                'name' => 'Tipul problemei',
                'type' => 'enum',
                'values' => task_get_types(),
                'default' => 'classic',
        ),
);

?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= htmlentities(url_task_edit($task_id)) ?>"
      method="post"
      class="task">
    <fieldset>
    <legend>Despre problema</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['title'], 'title') ?>
        <?= view_form_field_li($form_fields['page_name'], 'page_name') ?>
        <?= view_form_field_li($form_fields['author'], 'author') ?>
        <?= view_form_field_li($form_fields['source'], 'source') ?>
        <? if (identity_can('task-change-security', $task)) { ?> 
        <?= view_form_field_li($form_fields['hidden'], 'hidden') ?>
        <? } ?>
    </ul>
    </fieldset>

<?php
// FIXME: Field should be generated from task_get_types()
?>
    <fieldset>
    <legend>Detalii despre evaluare</legend>
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
                <input type="submit" value="Salveaza" id="form_submit" class="button important" />
            </li>
        </ul>
    </div>
</form>

<?php include('footer.php'); ?>
