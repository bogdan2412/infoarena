<?php
    include(CUSTOM_THEME . 'header.php');
?>

<h1><?= html_escape($title) ?></h1>

<p>Introdu numele de utilizator sau adresa de e-mail cu care te-ai înregistrat pe site.</p>
<p>Iți vom trimite pe e-mail instrucțiuni pentru a-ți reseta parola.</p>

<form action="<?= html_escape(url_resetpass()) ?>" method="post" class="login clear">
<fieldset>
    <legend>Date de identificare</legend>
    <ul class="form">
        <li>
            <label for="form_username">Cont de utilizator</label>
            <?= ferr_span('username') ?>
            <input type="text" name="username" id="form_username" value="<?= fval('username') ?>" />
            <span class="fieldHelp">dacă l-ai uitat, introdu doar adresa de e-mail</span>
        </li>

        <li>
            sau
        </li>

        <li>
            <label for="form_email">Adresa de e-mail</label>
            <?= ferr_span('email') ?>
            <input type="text" name="email" id="form_email" value="<?= fval('email') ?>" />
        </li>
    </ul>
</fieldset>
<ul class="form clear">
    <li>
        <input type="submit" value="Trimite-mi instrucțiuni" id="form_submit" class="button important" />
    </li>
</ul>
</form>

<?php wiki_include('template/resetarea-parolei'); ?>

<?php
    include('footer.php');
?>
