<form action="<?= html_escape(url_login()) ?>" method="post" class="login">
<table class="form">
  <tr>
    <td>
<fieldset>
    <legend><img src="<?= html_escape(url_static('images/icons/login.png')) ?>" alt="!" /> Autentificare</legend>
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
    </ul>
</fieldset>
    </td>
<?php
if (isset($view['captcha'])) {
?>
    <td>
<fieldset>
    <legend>Verificare</legend>
    <ul class="form">
        <li>
            <labe> Urmariti instructiunile de mai jos:</label>
            <?= ferr_span('captcha') ?>
            <?= $view['captcha'] ?>
        </li>
    </ul>
</fieldset>
    </td>
<?php
    }
?>
</tr>
</table>
<ul class="form clear">
  <li>
    <input type="submit" value="Autentificare" id="form_submit" class="button important" />
  </li>
</ul>
</form>

