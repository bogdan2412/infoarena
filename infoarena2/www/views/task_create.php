<?php include('header.php'); ?>

<h1><?= htmlentities($title) ?></h1>

<form action="<?= htmlentities(url_task_create()) ?>" method="post" class="task create clear">
    <ul class="form">
        <li id="field_id">
            <?= format_form_text_field('id', 'Id-ul problemei'); ?>
        </li>
<? /* FIXME: copy/paste eats babies. */ ?>
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
    <ul class="form clear">
        <li>
            <input type="submit" class="button important" value="Creaza task" id="form_submit" />
        </li>
    </ul>
</form>

<?php include('footer.php'); ?>
