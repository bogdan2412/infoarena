<?php include('views/utilities.php') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title><?= getattr($view, 'title') ?></title>

    <link type="text/css" rel="stylesheet" href="<?= url('static/css/default.css') ?>"/>
    <script type="text/javascript" src="<?= url('/static/js/MochiKit.js') ?>"></script>
    <script type="text/javascript" src="<?= url('static/js/default.js') ?>"></script>
</head>
<body>

<div id="header">
    <strong><a id="logo" href="<?= url('home') ?>">info-arena</a></strong>
    <span id="usp">informatica de performanta</span>
</div>

<div id="sidebar">
    <ul id="nav">
        <li><a href="<?= url('home') ?>">Home</a></li>
        <li><a href="<?= url('news') ?>">Stiri</a></li>
        <li><a href="<?= url('contests') ?>">Concursuri</a></li>
        <li><a href="<?= url('practice') ?>">Pregatire</a></li>
        <li><a href="<?= url('articles') ?>">Articole</a></li>
        <li><a href="<?= url('about') ?>">Despre info-arena</a></li>
    </ul>

    <div class="sidebox" id="members">
        <p class="title"><strong>Membri</strong></p>
        <a href="<?= url('register') ?>">Inregistreaza-te!</a>
    </div>
</div>

<div id="content">

<?php
    // display flash message
    if (isset($_SESSION['_flash'])) {
?>
    <div id="flash">
<?= $_SESSION['_flash'] ?>
    </div>
<?php
        unset($_SESSION['_flash']);
    }
?>

