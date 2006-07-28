<?php include('header.php'); ?>

<form enctype="multipart/form-data" action="<?= url($page_name) ?>" method="post">
<ul class="form">
    <li>
        <input type="hidden" name="action" value="attach-submit" />
        <label for="form_filename">Nume fisier:</label>
        <input type="file" name="file_name" value="<?= fval('file_name') ?>" id="form_filename" />
        <?php if (ferr('file_name')) { ?>
        <span class="fieldError"><?= ferr('file_name') ?></span>
        <?php } ?>
        <?php if (ferr('file_size')) { ?>
        <span class="fieldError"><?= ferr('file_size') ?></span>
        <?php } ?>        
    </li>

    <li>
        <input type="submit" class="button important" value="Ataseaza" id="form_submit" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
