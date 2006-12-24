<?php include(IA_ROOT.'www/views/header.php'); ?>

<h1><?= htmlentities($title) ?></h1>

<p>Daca nu esti inregistrat deja, te poti
<?= format_link(url_register(), "inregistra aici") ?>;
daca ti-ai uitat parola, o poti <a href="<?= htmlentities(url_resetpass()) ?>">reseta aici</a>.</p>

<?php include(IA_ROOT.'www/views/form_login.php'); ?>

<?php wiki_include('template/login'); ?>

<?php include(IA_ROOT.'www/views/footer.php'); ?>

