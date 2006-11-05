<?php
include('views/header.php'); 
?>

<h1><?= getattr($view, 'title') ?></h1>

<form action="<?= getattr($view, 'action') ?>" method="post" class="task">
    <h2>Despre problema</h2>

    <ul class="form">
        <li id="field_author">
            <label for="form_author">Autor(i)</label>
            <input type="text" name="author" value="<?= fval('author') ?>" id="form_author"/>
            <?= ferr_span('author') ?>
        </li>
        
        <li id="field_source">
            <label for="form_source">Sursa</label>
            <input type="text" name="source" value="<?= fval('source') ?>" id="form_source"/>
            <?= ferr_span('source') ?>
        </li>
        

        <li id="field_hidden">
            <label for="form_hidden">Vizibilitate</label>
                <select name="hidden" id="form_hidden">
                    <option value="1"<?= '1' == fval('hidden') ? ' selected="selected"' : '' ?>>Task ascuns</option>
                    <option value="0"<?= '0' == fval('hidden') ? ' selected="selected"' : '' ?>>Task public (vizibil)</option>
                </select>
                <?= ferr_span('hidden')?>
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
