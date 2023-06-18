<?php include(CUSTOM_THEME . 'header.php'); ?>

<h1><?= html_escape($view['title']) ?></h1>

<p>Dacă ești deja înregistrat te poți <a href="<?= html_escape(url_login()) ?>">autentifica aici</a>; dacă ți-ai uitat parola, o poți <a href="<?= html_escape(url_resetpass()) ?>">reseta aici</a>.</p>

<div id="sidebar2">
    <div class="section">
        <h3>De ce să te înregistrezi?</h3>
        <ul>
            <li>&hellip; ca să îți poți evalua soluțiile la problemele din arhivă;</li>
            <li>&hellip; ca să poți participa la concursuri;</li>
            <li>&hellip; ca să cunoști și alți oameni destepți. ;)</li>
        </ul>
    </div>

    <div class="section">
        <h3>Ce se întâmplă cu datele mele?</h3>
        <ul>
            <li>Adresa de e-mail <strong>nu</strong> se va afișa pe site și nu va fi divulgată altor părți.</li>
            <li>Numele tău complet va apărea în clasamente.</li>
        </ul>
    </div>

</div>

<form enctype="multipart" action="<?= html_escape($action) ?>" method="post" class="profile clear">
    <fieldset>
        <legend>
            <img src="<?= html_escape(url_static('images/icons/key.gif')) ?>" alt="!">
            Utilizator <?= SITE_NAME ?>
        </legend>
        <ul class="form">
            <li>
                <label for='form_username'>Nume cont utilizator</label>
                <?= ferr_span('username') ?>
                <input type="text" name="username" value="<?= fval('username') ?>" id="form_username">
                <span class="fieldHelp">Fără spații sau diacritice.</span>
            </li>
            <li>
                <label for='form_password'>Parolă</label>
                <?= ferr_span('password') ?>
                <input type="password" name="password" id="form_password">
                <span class="fieldHelp">Cel puțin 4 caractere.</span>
            </li>
            <li>
                <label for='form_password2'>Confirmare parolă</label>
                <?= ferr_span('password2') ?>
                <input type="password" name="password2" id="form_password2">
            </li>
        </ul>
    </fieldset>

    <!-- Hack for valid html (autocomplete is not in the spec). -->
    <script>
        <!--
                                                         document.getElementById("form_password").setAttribute("autocomplete", "off");
        document.getElementById("form_password2").setAttribute("autocomplete", "off");
                                                        -->
    </script>

    <fieldset>
        <legend>Informații personale</legend>
        <ul class="form">
            <li>
                <label for="form_full_name">Nume complet</label>
                <?= ferr_span('full_name') ?>
                <input type="text" name="full_name" value="<?= fval('full_name') ?>" id="form_full_name">
                <span class="fieldHelp">Conturile cu nume greșite sau false vor fi dezactivate.</span>
            </li>
            <li>
                <label for="form_email">Adresa de e-mail</label>
                <?= ferr_span('email') ?>
                <input type="text" name="email" value="<?= fval('email') ?>" id="form_email">
                <span class="fieldHelp">
                    Aici vei primi (în caz că dorești) mesaje de la alți
                    utilizatori, noutăți <?= SITE_NAME ?>.
                </span>
            </li>
        </ul>
    </fieldset>

    <?php
        if(isset($view['captcha'])) {
    ?>
        <fieldset>
            <legend>Verificare</legend>
            <ul class="form">
                <li>
                    <script>
                        var RecaptchaOptions = {
                            theme : 'clean',
                        };
                    </script>

                    <label>Scrieți cuvintele de mai jos:</label>
                    <?= ferr_span('captcha') ?>
                    <?= $view['captcha'] ?>
                    <span class="fieldHelp">Vă rugăm să transcrieți cuvintele de mai sus în această căsuță pentru verificare</span>
                </li>
            </ul>
        </fieldset>
    <?php
        }
    ?>

    <ul class="form clear">
        <li>
            <?php if (NEWSLETTER) : ?>
                <input
                    type="checkbox"
                    <?php if (fval('newsletter')) echo 'checked="checked"'; ?>
                    name="newsletter"
                    id="form_newsletter">
                <label for="form_newsletter" class="checkbox">
                    Mă abonez la newsletter. Sunt de acord să primesc pe
                    e-mail noutăți despre <?= SITE_NAME ?>. Mă pot dezabona oricând.
                </label>
            <?php else : ?>
                <input type="hidden" name="newsletter" value="0">
            <?php endif; ?>
        </li>
        <li>
            <?= ferr_span('tnc') ?>
            <input type="checkbox" <?php if (fval('tnc'))
                                       echo 'checked="checked"'; ?> name="tnc" id="form_tnc">
            <label for="form_tnc" class="checkbox">Sunt de acord cu <a href="<?= html_escape(url_textblock('termeni-si-conditii')) ?>">termenii și condițiile de utilizare</a> ale site-ului <?= SITE_NAME ?>.</label>
        </li>
        <li>
            <input type="submit" value="Înregistrează-mă" id="form_submit" class="button important">
        </li>
    </ul>

</form>

<?php include('footer.php'); ?>
