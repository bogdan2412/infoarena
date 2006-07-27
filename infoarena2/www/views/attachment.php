<?php include('header.php'); ?>

<form enctype="multipart/form-data" action="<?= url('attachment/save/').getattr($view, 'action') ?>" method="post">
<ul class="form">
    <li>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= fval('max_file_size') ?>" />
        <label for="form_filename">Nume fisier:</label>
        <input type="file" name="file_name" value="<?= fval('file_name') ?>" id="form_filename" />
        <?php if (getattr($errors, 'file_name')) { ?>
        <span class="fieldError"><?= getattr($errors, 'file_name') ?></span>
        <?php } ?>
        <?php if (getattr($errors, 'file_size')) { ?>
        <span class="fieldError"><?= getattr($errors, 'file_size') ?></span>
        <?php } ?>        
    </li>

    <li>
        <input type="submit" value="Ataseaza" id="form_submit" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
