<?php include(CUSTOM_THEME . 'header.php'); ?>

<h1>Atașează la pagina <?= format_link(url_textblock($view['page_name']), $view['page_name']) ?></h1>

<form enctype="multipart/form-data"
      action="<?= html_escape(url_attachment_new($page_name)) ?>"
      method="post"
      class="clear">
<fieldset>
    <legend>Alege fișier </legend>
    <ul class="form">
        <li>
            <label for="form_files">Fișier/Fișiere</label>
            <?= ferr_span('files') ?>
            <?= ferr_span('file_size') ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= IA_ATTACH_MAXSIZE ?>" />
            <input type="file" name="files[]" value="<?= fval('files') ?>" id="form_files" size="50" multiple="" />

            <span class="fieldHelp">Dimensiunea maximă admisă este de <?= IA_ATTACH_MAXSIZE / 1024 / 1024 ?>MB.</span>
            <span class="fieldHelp">Numele fișierului nu poate conține spații.</span>
        </li>

        <br/>
        <li>
            <input type="checkbox" name="autoextract" value="1" <?= fval_checkbox('autoextract') ?> id="form_autoextract" class="checkbox" />
            <label class="checkbox" for="form_autoextract">Expandează arhiva .zip</label>
            <span class="fieldHelp">Trimite o arhivă Zip cu unul sau mai multe fișiere. Se va crea câte un atașament pentru fiecare fișier din arhivă.</span>
        </li>

        <br/>
        <li>
            <span class="fieldHelp"><?= format_link(url_attachment_list($page_name), "Listează celelalte atașamente") ?></span>
        </li>
    </ul>
</fieldset>

<ul class="form clear">
    <li>
        <input type="submit" class="button important" value="Atașează" id="form_submit" />
    </li>
</ul>

</form>

<?php include('footer.php'); ?>
