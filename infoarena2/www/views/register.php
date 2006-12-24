<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>

<p>Daca esti deja inregistrat te poti <a href="<?= htmlentities(url_login()) ?>">autentifica aici</a>; daca ti-ai uitat parola, o poti <a href="<?= htmlentities(url_resetpass()) ?>">reseta aici</a>.</p>

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

<form enctype="multipart" action="<?= htmlentities($action) ?>" method="post" class="profile clear">
<fieldset>
    <legend><img src="<?= htmlentities(url_static('images/icons/key.gif')) ?>"/> Utilizator infoarena</legend>
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
            <input autocomplete="off" type="password" name='password' id="form_password" />
            <span class="fieldHelp">Cel putin 4 caractere</span>
        </li>
        <li>
            <label for='form_password2'>Confirmare parola</label>
            <?= ferr_span('password2') ?>
            <input autocomplete="off" type="password" name='password2' id="form_password2" />
        </li>
    <ul>
</fieldset>

<fieldset>
    <legend>Informatii personale</legend>
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
        <?= ferr_span('tnc') ?>
        <input type="checkbox" <?php if (fval('tnc'))
            echo 'checked="checked"'; ?> name="tnc" id="form_tnc"/>
        <label for="form_tnc" class="checkbox">Sunt de acord cu <a href="<?= htmlentities(url_textblock('termeni-si-conditii')) ?>">termenii si conditiile de utilizare</a> ale site-ului infoarena</label>
    </li>
    <li>
        <input type="submit" value="Inregistreaza-ma" id="form_submit" class="button important" />
    </li>
</form>

<?php include('footer.php'); ?>
