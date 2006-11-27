<?php include(IA_ROOT.'www/views/header.php'); ?>

<h1><?= htmlentities($title) ?></h1>

<p>Daca nu esti inregistrat deja, te poti <a href="<?= url('register') ?>">inregistra aici</a>; daca ti-ai uitat parola, o poti <a href="<?= url('resetpass') ?>">reseta aici</a>.</p>

<?php include(IA_ROOT.'www/views/form_login.php'); ?>

<?php wiki_include('template/login'); ?>

<?php include(IA_ROOT.'www/views/footer.php'); ?>

