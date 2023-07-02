<?php
  include(IA_ROOT_DIR . 'www/views/header.php');
?>

<h1><?= html_escape($title) ?></h1>

<p>Dacă nu ești înregistrat deja, te poți
<?= format_link(url_register(), "înregistra aici") ?>;
daca ți-ai uitat parola, o poți <a href="<?= html_escape(url_resetpass()) ?>">reseta aici</a>.</p>

<?php include(IA_ROOT_DIR . 'www/views/form_login.php'); ?>

<?php Wiki::include('template/login'); ?>

<?php include(IA_ROOT_DIR . 'www/views/footer.php'); ?>
