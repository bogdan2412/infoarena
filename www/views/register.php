<?php include('header.php'); ?>

<h1><?= html_escape($view['title']) ?></h1>

<p>Daca esti deja inregistrat te poti <a href="<?= html_escape(url_login()) ?>">autentifica aici</a>; daca ti-ai uitat parola, o poti <a href="<?= html_escape(url_resetpass()) ?>">reseta aici</a>.</p>

<div id="sidebar2">
    <div class="section">
        <h3>De ce sa te inregistrezi?</h3>
        <ul>
            <li>&hellip; ca sa iti poti evalua solutiile la problemele din arhiva</li>
            <li>&hellip; ca sa poti participa la concursuri</li>
            <li>&hellip; ca sa cunosti si alti oameni destepti ;)</li>
        </ul>
    </div>

    <div class="section">
        <h3>Ce se intampla cu datele mele?</h3>
        <ul>
            <li>Adresa de e-mail <strong>nu</strong> se va afisa pe site si nu va fi divulgata altor parti.</li>
            <li>Numele tau complet va aparea in clasamente.</li>
        </ul>
    </div>

</div>

<form enctype="multipart" action="<?= html_escape($action) ?>" method="post" class="profile clear">
    <fieldset>
        <legend>
            <img src="<?= html_escape(url_static('images/icons/key.gif')) ?>" alt="!" />
            Utilizator <?= SITE_NAME ?>
        </legend>
        <ul class="form">
            <li>
                <label for='form_username'>Nume cont utilizator</label>
                <?= ferr_span('username') ?>
                <input type="text" name="username" value="<?= fval('username') ?>" id="form_username" />
                <span class="fieldHelp">Fara spatii sau diacritice</span>
            </li>
            <li>
                <label for='form_password'>Parola</label>
                <?= ferr_span('password') ?>
                <input type="password" name="password" id="form_password" />
                <span class="fieldHelp">Cel putin 4 caractere</span>
            </li>
            <li>
                <label for='form_password2'>Confirmare parola</label>
                <?= ferr_span('password2') ?>
                <input type="password" name="password2" id="form_password2" />
            </li>
        </ul>
    </fieldset>

    <!-- Hack for valid html (autocomplete is not in the spec). -->
    <script language="JavaScript" type="text/javascript">
        <!--
                                                         document.getElementById("form_password").setAttribute("autocomplete", "off");
        document.getElementById("form_password2").setAttribute("autocomplete", "off");
                                                        -->
    </script>

    <fieldset>
        <legend>Informatii personale</legend>
        <ul class="form">
            <li>
                <label for="form_full_name">Nume complet</label>
                <?= ferr_span('full_name') ?>
                <input type="text" name="full_name" value="<?= fval('full_name') ?>" id="form_full_name" />
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

    <?php
        if(isset($view['captcha'])) {
    ?>
        <fieldset>
            <legend>Verificare</legend>
            <ul class="form">
                <li>
                    <script type="text/javascript">
                        var RecaptchaOptions = {
                            theme : 'clean',
                        };
                    </script>

                    <label>Scrieti cuvintele de mai jos:</label>
                    <?= ferr_span('captcha') ?>
                    <?= $view['captcha'] ?>
                    <span class="fieldHelp">Va rugam sa transcrieti cuvintele de mai sus in aceasta casuta pentru verificare</span>
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
                    id="form_newsletter"/>
                <label for="form_newsletter" class="checkbox">
                    Ma abonez la newsletter. Sunt de acord sa primesc pe
                    e-mail noutati despre <?= SITE_NAME ?>. Ma pot dezabona oricand.
                </label>
            <?php else : ?>
                <input type="hidden" name="newsletter" value="0">
            <?php endif; ?>
        </li>
        <li>
            <?= ferr_span('tnc') ?>
            <input type="checkbox" <?php if (fval('tnc'))
                                       echo 'checked="checked"'; ?> name="tnc" id="form_tnc"/>
            <label for="form_tnc" class="checkbox">Sunt de acord cu <a href="<?= html_escape(url_textblock('termeni-si-conditii')) ?>">termenii si conditiile de utilizare</a> ale site-ului infoarena</label>
        </li>
        <li>
            <input type="submit" value="Inregistreaza-ma" id="form_submit" class="button important" />
        </li>
    </ul>

</form>

<?php include('footer.php'); ?>
