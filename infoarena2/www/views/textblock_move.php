<?php include('header.php'); ?>

<h1><?= $title ?></h1>

<form action="<?= url($page_name) ?>" method="post">
    <input type="hidden" name="action" value="move-submit" />

    <ul class="form">
        <li>
            <label for="form_new_name">Noul nume:</label>
            <input type="text" name="new_name" value="<?= fval('new_name') ?>" id="form_filename" />
            <?php if (ferr('new_name')) { ?>
            <span class="fieldError"><?= ferr('new_name') ?></span>
            <?php } ?>
        </li>

        <li>
            <input type="submit" class="button important" value="Muta" id="form_submit" />
        </li>
    </ul>
</form>

<?php include('footer.php'); ?>
