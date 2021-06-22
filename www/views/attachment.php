<?php include(CUSTOM_THEME.'header.php'); ?>

<h1>Ataseaza la pagina <?= format_link(url_textblock($view['page_name']), $view['page_name']) ?></h1>

<form enctype="multipart/form-data"
      action="<?= html_escape(url_attachment_new($page_name)) ?>"
      method="post"
      class="clear">
<fieldset>
    <legend>Alege fisier </legend>
    <ul class="form">
        <li>
            <label for="form_files">Fisier/Fisiere</label>
            <?= ferr_span('files') ?>
            <?= ferr_span('file_size') ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= IA_ATTACH_MAXSIZE ?>" />
            <input type="file" name="files[]" value="<?= fval('files') ?>" id="form_files" size="50" multiple="" />

            <span class="fieldHelp">Dimensiunea maxima admisa este de <?= IA_ATTACH_MAXSIZE / 1024 / 1024 ?>MB.</span>
            <span class="fieldHelp">Numele fisierului nu poate contine spatii.</span>
        </li>

        <br/>
        <li>
            <input type="checkbox" name="autoextract" value="1" <?= fval_checkbox('autoextract') ?> id="form_autoextract" class="checkbox" />
            <label class="checkbox" for="form_autoextract">Expandeaza arhiva .zip</label>
            <span class="fieldHelp">Trimite o arhiva ZIP cu unul sau mai multe fisiere. Se va crea cate un atasament pentru fiecare fisier din arhiva. (Bifati aceasta optiune daca uploadati un singur fisier)</span>
        </li>

        <br/>
        <li>
            <span class="fieldHelp"><?= format_link(url_attachment_list($page_name), "Listeaza celelalte atasamente...") ?></span>
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
