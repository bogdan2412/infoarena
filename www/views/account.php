<?php
require_once(IA_ROOT_DIR.'www/views/utilities.php');
include('header.php');
include('tags_header.php');
?>

<h1><?= html_escape($view['title']) ?></h1>

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

<form enctype="multipart/form-data" action="<?= html_escape($action) ?>" method="post" class="profile clear" <?= tag_form_event() ?>>

<fieldset>
    <legend><img src="<?= html_escape(url_static('images/icons/key.gif')) ?>" alt="!" /> Informatii legate de securitate</legend>
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

<?php
if (array_key_exists('security_level', $form_values)) {
    echo view_form_field_li(array(
            'name' => 'Nivel de securitate',
            'type' => 'enum',
            'values' => array(
                    'normal' => 'Utilizator normal',
                    'helper' => 'Propunator de probleme',
                    'intern' => 'Intern',
                    'admin' => 'Admin smenar',
            ),
            'default' => 'normal',
    ), 'security_level');
}
?>
    </ul>
</fieldset>

<fieldset>
    <legend>Schimba informatii personale</legend>
    <ul class="form">
    <?php if (identity_can('user-tag', $view['user'])) { ?>
        <?= tag_format_input_box(array("label" => "Tag-uri", "name" => "tags"), fval('tags')) ?>
    <?php } ?>
        </li>
            <label for="form_name">Nume complet</label>
            <?= ferr_span('full_name') ?>
            <input type="text" name="full_name" value="<?= fval('full_name') ?>" id="form_name" />
            <span class="fieldHelp">Conturile cu nume gresite sau false vor fi dezactivate</span>
        </li>
        <li>
            <label for="form_email">Adresa e-mail</label>
            <?= ferr_span('email') ?>
            <input type="text" name="email" value="<?= fval('email') ?>" id="form_email" />
            <span class="fieldHelp">
                Aici vei primi (in caz ca doresti) mesaje de la alti
                utilizatori, noutati <?= SITE_NAME ?>
            </span>
        </li>
    </ul>
</fieldset>

<fieldset>
    <legend>Schimba avatar</legend>
    <ul class="form">
        <li>
            <?php
                // display avatar
                $avatar_url = url_user_avatar($user['username'], "big");
                echo '<img class="avatar" src="'.html_escape($avatar_url).'" alt="avatar"/>';
            ?>
        </li>
        <li>
            <?php
                if ($view['avatar_exists']) {
                    echo format_post_link(url_attachment_delete(
                                IA_USER_TEXTBLOCK_PREFIX . $user['username'],
                                'avatar'), "Sterge Avatar", array(), true,
                                array('onclick' => "return confirm('Aceasta " .
                                        'actiune este ireversibila! Doresti ' .
                                        "sa continui?')"));
                }
            ?>
            <label for="form_avatar">Avatar nou</label>
            <?= ferr_span('avatar') ?>
            <input type="file" name="avatar" id="form_avatar" />
            <span class="fieldHelp">O poza in format JPEG, PNG sau GIF, dimensiune maxima <?= IA_AVATAR_MAXSIZE/1024 . "KB" ?></span>
        </li>
    </ul>
</fieldset>

<ul class="form clear">
    <li>
        <?php if (NEWSLETTER) : ?>
            <input
                type="checkbox"
                <?php if (fval('newsletter')) echo 'checked="checked"'; ?>
                name="newsletter"
                id="form_newsletter"/>
            <label for="form_newsletter" class="checkbox">
                Ma abonez la newsletter. Sunt de acord sa primesc pe e-mail
                noutati despre <?= SITE_NAME ?>. Ma pot dezabona oricand.
            </label>
        <?php else : ?>
            <input type="hidden" name="newsletter" value="0">
        <?php endif; ?>
    </li>
    <li>
        <input type="submit" value="Salveaza" id="form_submit" class="button important" />
    </li>
</ul>
</form>

<?php include('footer.php'); ?>
