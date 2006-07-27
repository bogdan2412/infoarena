<?php include('header.php'); ?>

<form action="<?= url('register/save') ?>" method="post">
<ul class="form">
    <li>
        <label for="form_name">Nume complet</label>
        <input type="text" name="full_name" value="<?= fval('full_name') ?>" id="form_name" />
        <?php if (getattr($errors, 'full_name')) { ?>
        <span class="fieldError"><?= getattr($errors, 'full_name') ?></span>
        <?php } ?>
    </li>

    <li>
        <label for="form_email">Adresa e-mail</label>
        <input type="text" name="email" value="<?= fval('email') ?>" id="form_email" />
        <?php if (getattr($errors, 'email')) { ?>
        <span class="fieldError"><?= getattr($errors, 'email') ?></span>
        <?php } ?>
    </li>

    <li>
        <input type="submit" value="Inregistreaza-ma" id="form_submit" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
