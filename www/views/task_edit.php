<?php
require_once(IA_ROOT_DIR."common/task.php");
require_once(IA_ROOT_DIR."common/tags.php");
require_once(IA_ROOT_DIR."www/format/form.php");

$view['head'] = getattr($view, 'head').
    "<script type=\"text/javascript\" src=\"" . html_escape(url_static("js/parameditor.js")) . "\"></script>";

include('views/header.php');
include('views/tags_header.php');

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
        'open_source' => array(
                'name' => 'Acces liber la surse',
                'type' => 'bool'
        ),
        'open_tests' => array(
                'name' => 'Acces liber la teste',
                'type' => 'bool'
        ),
);

?>

<h1>Editare <a href="<?= html_escape(url_task($view['task_id'])) ?>"><?= html_escape($view['title']) ?></a></h1>

<?php if (identity_can("task-delete", $task)) { ?>
<form action="<?= html_escape(url_task_delete()) ?>" method="post" style="float: right">
    <input type="hidden" name="task_id" value="<?= html_escape($task_id) ?>" />
    <input onclick="return confirm('Aceasta actiune este ireversibila! Doresti sa continui?')") type="submit" value="Sterge problema" id="form_delete" class="button important" />
</form>
<?php } ?>

<form action="<?= html_escape(url_task_edit($task_id)) ?>"
      method="post"
      class="task"
      <?= tag_form_event() ?>>
    <fieldset>
    <legend>Despre problema</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['title'], 'title') ?>
        <?= view_form_field_li($form_fields['page_name'], 'page_name') ?>
        <?= view_form_field_li($form_fields['author'], 'author') ?>
        <?= view_form_field_li($form_fields['source'], 'source') ?>
        <?php if (identity_can('task-change-security', $task)) { ?> 
           <?= view_form_field_li($form_fields['hidden'], 'hidden') ?>
        <?php } ?>
        <?php if (identity_can('task-tag', $task)) { ?>
           <?= tag_format_input_box(fval('tags')) ?>
        <?php } ?>
    </ul>
    </fieldset>

<?php
// FIXME: Field should be generated from task_get_types()
?>
    <?php if (identity_can('task-change-open', $task)) { ?> 
    <fieldset>
    <legend>Acces la surse si teste</legend>
    <ul class="form">
        <?= view_form_field_li($form_fields['open_source'], 'open_source') ?>
        <?= view_form_field_li($form_fields['open_tests'], 'open_tests') ?>
    </ul>
    </fieldset>
    <?php } ?>

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
