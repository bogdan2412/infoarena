<form action="<?= htmlentities(url('login')) ?>" method="post" class="login">
<fieldset>
    <legend>Autentificare</legend>
    <ul class="form">
        <li>
            <label for="form_username">Cont de utilizator</label>
            <input type="text" name="username" id="form_username" value="<?= fval('username') ?>" />
        </li>
        <li>
            <label for="form_password">Parola</label>
            <input type="password" name="password" id="form_password" value="<?= fval('password') ?>" />
        </li>
        <li>
            <input type="checkbox" value="on" id="form_remember" name="remember" class="checkbox"<?= fval('remember') ? ' checked="checked"' : '' ?>/>
            <label class="checkbox" for="form_remember">Pastreaza-ma autentificat 5 zile</label>
        </li>
        <li>
            <input type="submit" value="Autentificare" id="form_submit" class="button important" />
        </li>
    </ul>
</fieldset>
</form>

