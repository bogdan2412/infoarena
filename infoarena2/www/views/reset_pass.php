<?php
    include('header.php');
?>

<form action="<?= url('reset_pass/reset') ?>" method="post" class="login">
<ul class="form">
    <div class="fieldHelp">Introduceti numele de utilizator sau emailul inregistrat pe site</div>
    <li>
        <label for="form_username">Utilizator (nume cont)</label>
        <input type="text" name="username" id="form_username" value="<?= fval('username') ?>" />

        <?= ferr_span('username') ?>
    </li>
    
    <li>
        <label for="form_email">Email inregistrat pe site</label>
        <input type="text" name="email" id="form_email" value="<?= fval('email') ?>" />

        <?= ferr_span('email') ?>
    </li>
    
    <li>
        <input type="submit" value="submit" id="form_submit" class="button important" />
    </li>
</ul>
</form>

<?php
    include('footer.php');
?>