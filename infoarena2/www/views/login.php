<?php
    include('header.php');
?>
<form action="<?= getattr($view, 'action') ?>" method="post" class="login">
<ul class="form">
    <li>
        <label for="form_username">Utilizator (nume cont)</label>
        <input type="text" name="username" id="form_username" value="<?= fval('username') ?>" />
    </li>
    
    <li>
        <label for="form_password">Parola</label>
        <input type="password" name="password" id="form_password" value="<?= fval('password') ?>" />
    </li>
    
    <li>
        <input type="submit" value="Autentificare" id="form_submit" class="button important" />
        <a href="<?= url("reset_pass") ?>">Am uitat parola</a>
    </li>
</ul>
</form>

<?php
    include('footer.php');
?>

