<?php include(CUSTOM_THEME . 'header.php'); ?>

<h1><?= html_escape($title) ?></h1>

<form action="<?= html_escape(url_textblock_copy($page_name)) ?>" method="post" class="copy clear">
<fieldset>
    <ul class="form">
        <li>
            <label for="form_new_name">Noul nume</label>
            <?= ferr_span('new_name') ?>
            <input type="text" name="new_name" value="<?= fval('new_name') ?>" id="form_new_name" />
        </li>
    </ul>
</fieldset>
<ul class="form clear">
    <li>
        <input type="submit" class="button important" value="Copiază pagina" id="form_submit" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
