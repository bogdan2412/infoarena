<form action="<?= htmlentities(url_login()) ?>" method="post" class="login">
<fieldset>
    <legend><img src="<?= htmlentities(url_static('images/icons/key.gif')) ?>" alt="Yellow key, just a decoration" /> Autentificare</legend>
    <ul class="form">
<?= view_form_field_li(array(
        'name' => 'Cont de utilizator',
        'type' => 'string',
        'access_key' => 'c',
), 'username') ?>
<?= view_form_field_li(array(
        'name' => 'Parola',
        'type' => 'string',
        'is_password' => true,
        'access_key' => 'p',
), 'password') ?>
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

