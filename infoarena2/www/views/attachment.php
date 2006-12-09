<?php include('header.php'); ?>

<h1><?= htmlentities($title) ?></h1>

<form enctype="multipart/form-data" action="<?= htmlentities(url($page_name)) ?>" method="post" class="clear">
<fieldset>
    <legend>Alege fisier</legend>
    <ul class="form">
        <li>
            <input type="hidden" name="action" value="attach-submit" />
            <label for="form_filename">Fisier</label>
            <?= ferr_span('file_name') ?>
            <?= ferr_span('file_size') ?>
            <input type="file" name="file_name" value="<?= fval('file_name') ?>" id="form_filename" />

            <span class="fieldHelp">Dimensiunea maxima admisa este de <?= IA_ATTACH_MAXSIZE/1024/1024 ?>MB.</span>
            <span class="fieldHelp">Numele fisierului nu poate contine spatii.</span>
        </li>

        <li>
            <input type="checkbox" name="autoextract" value="1" <?= fval_checkbox('autoextract') ?> id="form_autoextract" class="checkbox" />
            <label class="checkbox" for="form_autoextract">Expandeaza arhiva .zip</label>
            <span class="fieldHelp">Trimite o arhiva ZIP cu unul sau mai multe fisiere. Se va crea cate un atasament pentru fiecare fisier din arhiva.</span>
        </li>
    </ul>
</fieldset>

<ul class="form clear">
    <li>
        <input type="submit" class="button important" value="Ataseaza" id="form_submit" />
    </li>
</ul>

</form>

<?php include('footer.php'); ?>
