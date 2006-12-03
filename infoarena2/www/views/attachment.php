<?php include('header.php'); ?>

<h1><?= htmlentities($title) ?></h1>

<form enctype="multipart/form-data" action="<?= htmlentities(url($page_name)) ?>" method="post" class="hollyfix">
<input type="hidden" name="auto_extract" value="1" />

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
</fieldset>

<ul class="form">
    <li>
        <input type="submit" class="button important" value="Ataseaza" id="form_submit" />
    </li>
</ul>

</form>

<?php include('footer.php'); ?>
