<?php
require_once(IA_ROOT."common/task.php");
require_once(IA_ROOT."www/format/form.php");

$view['head'] = getattr($view, 'head').
    "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/parameditor.js")) . "\"></script>";

include('views/header.php');

// Validate task.
log_assert_valid(task_validate($task));
?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= htmlentities(url_task_edit($task_id)) ?>"
      method="post"
      class="task">
    <fieldset>
    <legend>Despre problema</legend>
    <ul class="form">
        <li id="field_title">
            <?= format_form_text_field('title', 'Titlu') ?>
        </li>

        <li id="field_page_name">
            <?= format_form_text_field('page_name', 'Pagina cu enuntul') ?>
        </li>
 
        <li id="field_author">
            <?= format_form_text_field('author', 'Autor(i)') ?>
        </li>

        <li id="field_source">
            <?= format_form_text_field('source', 'Sursa') ?>
        </li>

<? if (identity_can('task-change-security', $task)) { ?>
        <li id="field_hidden">
            <label for="form_hidden">Vizibilitate</label>
            <select name="hidden" id="form_hidden">
                <option value="1"<?= '1' == fval('hidden') ? ' selected="selected"' : '' ?>>Task ascuns</option>
                <option value="0"<?= '0' == fval('hidden') ? ' selected="selected"' : '' ?>>Task public (vizibil)</option>
            </select>
            <?= ferr_span('hidden')?><br />
        </li>
<? } ?>
    </ul>
    </fieldset>

<?php
// FIXME: Field should be generated from task_get_types()
?>

    <fieldset>
    <legend>Detalii despre evaluare</legend>
    <ul class="form">
        <li id="field_type">
            <label for="form_type">Tipul problemei</label>
                <select name="type" id="form_type">
                    <option value=""<?= '' == fval('type') ? ' selected="selected"' : '' ?>>[ Alege ]</option>
                    <option value="classic"<?= 'classic' == fval('type') ? ' selected="selected"' : '' ?>>Clasic</option>
                    <option value="output-only"<?= 'output-only' == fval('type') ? ' selected="selected"' : '' ?>>Output unic</option>
                </select>
            <?= ferr_span('type')?>
        </li>
        <li>
            <hr />
        </li>

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
