<?php php
require_once(IA_ROOT_DIR.'www/views/utilities.php');
include('header.php');
?>

<h1><?= htmlentities($view['title']) ?></h1>

<div id="sidebar2">
<div class="section">
    <h3>Avatar</h3>

    <p>Uploadeaza-ti o poza care sa apara in clasamete, pe pagina ta si pe forum.</p>
</div>

<div class="section">
<h3>Ce se intampla cu datele mele?</h3>
<ul>
    <li>Adresa de e-mail <strong>nu</strong> se va afisa pe site si nu va fi divulgata altor parti.</li>
    <li>Numele tau complet va aparea in clasamente.</li>
</ul>
</div>

</div>

<form enctype="multipart/form-data" action="<?= htmlentities($action) ?>" method="post" class="profile clear">
<fieldset>
    <legend><img src="<?= htmlentities(url_static('images/icons/key.gif')) ?>" alt="!" /> Informatii legate de securitate</legend>
    <ul class="form">
        <li>
            <label for='form_passwordold'>Parola curenta</label>
            <?= ferr_span('passwordold') ?>
            <input type="password" name='passwordold' id="form_passwordold" />
            <span class="fieldHelp">Completeaza doar daca vrei sa schimbi parola sau adresa de e-mail</span>
        </li>
        <li>
            <label for='form_password'>Parola noua</label>
            <?= ferr_span('password') ?>
            <input type="password" name='password' id="form_password" />
            <span class="fieldHelp">Cel putin 4 caractere</span>
        </li>
        <li>
            <label for='form_password2'>Confirmare parola noua</label>
            <?= ferr_span('password2') ?>
            <input type="password" name='password2' id="form_password2" />
        </li>
<?
if (array_key_exists('security_level', $form_values)) {
    echo view_form_field_li(array(
            'name' => 'Nivel de securitate',
            'type' => 'enum',
            'values' => array(
                    'normal' => 'Utilizator normal',
                    'helper' => 'Propunator de probleme',
                    'admin' => 'Admin smenar',
            ),
            'default' => 'normal',
    ), 'security_level');
}
?>
    </ul>
</fieldset>

<!-- Hack for valid html (autocomplete is not in the spec). -->
<script language="JavaScript" type="text/javascript">
<!--
    document.getElementById("form_passwordold").setAttribute("autocomplete", "off");
    document.getElementById("form_password").setAttribute("autocomplete", "off");
    document.getElementById("form_password2").setAttribute("autocomplete", "off");
-->
</script>

<fieldset>
    <legend>Schimba avatar</legend>
    <ul class="form">
        <li>
            <?php
                // display avatar
                $avatar_url = url_user_avatar($user['username'], "150x150");
                echo '<img class="avatar" src="'.htmlentities($avatar_url).'" alt="avatar"/>';
            ?>
        </li>
        <li>
            <label for="form_avatar">Avatar nou</label>
            <?= ferr_span('avatar') ?>
            <input type="file" name="avatar" id="form_avatar" />
            <span class="fieldHelp">O poza in format JPEG, PNG sau GIF, dimensiune maxima <?= IA_AVATAR_MAXSIZE/1024 . "KB" ?></span>
        </li>
    </ul>
</fieldset>

<fieldset>
    <legend>Schimba informatii personale</legend>
    <ul class="form">
        <li>
            <label for="form_name">Nume complet</label>
            <?= ferr_span('full_name') ?>
            <input type="text" name="full_name" value="<?= fval('full_name') ?>" id="form_name" />
            <span class="fieldHelp">Conturile cu nume gresite sau false vor fi dezactivate</span>
        </li>
        <li>
            <label for="form_email">Adresa e-mail</label>
            <?= ferr_span('email') ?>
            <input type="text" name="email" value="<?= fval('email') ?>" id="form_email" />
            <span class="fieldHelp">Aici vei primi (in caz ca doresti) mesaje de la alti utilizatori, noutati infoarena</span>
        </li>
    </ul>
</fieldset>

<ul class="form clear">
    <li>
        <input type="checkbox" <?php if (fval('newsletter'))
            echo 'checked="checked"'; ?> name="newsletter" id="form_newsletter"/>
        <label for="form_newsletter" class="checkbox">Ma abonez la newsletter. Sunt de acord sa primesc pe e-mail noutati despre infoarena. Ma pot dezabona oricand.</label>
    </li>
    <li>
        <input type="submit" value="Salveaza" id="form_submit" class="button important" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
