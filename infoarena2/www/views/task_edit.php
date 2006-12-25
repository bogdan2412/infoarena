<?php
require_once(IA_ROOT."common/task.php");
$view['head'] = getattr($view, 'head').
    "<script type=\"text/javascript\" src=\"" . htmlentities(url_static("js/parameditor.js")) . "\"></script>";
include('views/header.php');
?>

<h1><?= htmlentities(getattr($view, 'title')) ?></h1>

<form action="<?= htmlentities(url_task_edit($task_id)) ?>"
      method="post"
      class="task">
    <h2>Despre problema</h2>

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
            <label for="form_source">Sursa</label>
            <?= format_form_text_field('source', 'Sursa') ?>
        </li>
 
        <li id="field_hidden">
            <label for="form_hidden">Vizibilitate</label>
                <select name="hidden" id="form_hidden">
                    <option value="1"<?= '1' == fval('hidden') ? ' selected="selected"' : '' ?>>Task ascuns</option>
                    <option value="0"<?= '0' == fval('hidden') ? ' selected="selected"' : '' ?>>Task public (vizibil)</option>
                </select>
            <?= ferr_span('hidden')?><br />
        </li>

<?php
// FIXME: Field should be generated from task_get_types()
?>

        <li id="field_type">
            <label for="form_type">Tip task</label>
                <select name="type" id="form_type">
                    <option value=""<?= '' == fval('type') ? ' selected="selected"' : '' ?>>[ Alege ]</option>
                    <option value="classic"<?= 'classic' == fval('type') ? ' selected="selected"' : '' ?>>Clasic</option>
                    <option value="output-only"<?= 'output-only' == fval('type') ? ' selected="selected"' : '' ?>>Output Only</option>
                </select>
            <?= ferr_span('type')?>
        </li>

        <li id="field_type">
            <label>Parametri</label>
            <? include('views/param_edit.php') ?>
        </li>
    </ul>
    <div class="submit">
        <ul class="form">
            <li id="form_submit">
                <input type="submit" value="Salveaza" id="form_submit" class="button important" />
            </li>
        </ul>
    </div>
</form>

<?php include('footer.php'); ?>
