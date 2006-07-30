<?php check_view($view); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title><?= htmlentities(getattr($view, 'title')) ?></title>

    <link type="text/css" rel="stylesheet" href="<?= url('static/css/default.css') ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= url('static/css/tabber.css') ?>"/>
    <script type="text/javascript" src="<?= url('static/js/config.js.php') ?>"></script>
    <script type="text/javascript" src="<?= url('static/js/MochiKit.js') ?>"></script>
    <script type="text/javascript" src="<?= url('static/js/default.js') ?>"></script>
    <script type="text/javascript" src="<?= url('static/js/tabber-minimized.js') ?>"></script>

    <?= getattr($view, 'head') ?>
</head>
<body<?= getattr($view, 'body_onload') ? ' onload="' . htmlentities(getattr($view, 'body_onload')) . '"' : '' ?>>

<div id="header">
    <strong><a id="logo" href="<?= url('home') ?>">info-arena</a></strong>
    <span id="usp">informatica de performanta</span>

    <div id="userbox">
<?php if (identity_anonymous()) { ?>
<a href="<?= url("login") ?>">&raquo; autentificare</a>
<?php } else { ?>
<strong><?= $identity_user['full_name'] ?></strong>
<a href="<?= url("logout") ?>">inchide &raquo;</a>
<?php } ?>

    </div>
</div>

<div id="sidebar">
    <ul id="nav">
        <li><a href="<?= url('home') ?>">Home</a></li>
        <li><a href="<?= url('news') ?>">Stiri</a></li>
        <li><a href="<?= url('contests') ?>">Concursuri</a></li>
        <li><a href="<?= url('practice') ?>">Pregatire</a></li>
        <li><a href="<?= url('articles') ?>">Articole</a></li>
        <li><a href="<?= url('about') ?>">Despre info-arena</a></li>
        <?php if (identity_can('edit-profile')) { ?>
        <li><a href="<?= url('profile') ?>">Modificare profil</a></li>
        <? } ?>
    </ul>
    <?php if (identity_anonymous()) { ?>
    <form action="<?= url('login', 'action=login') ?>" method="post" class="login">
        <div class="sidebox" id="members">
            <p class="title"><strong>Membri</strong></p>
            <ul class="form">
                <li>
                    <label for="form_username">Utilizator</label>
                    <input type="text" name="username" id="form_username" value="<?= fval('username') ?>" />
            
                    <?= ferr_span('username') ?>
                </li>
                
                <li>
                    <label for="form_password">Parola</label>
                    <input type="password" name="password" id="form_password" value="<?= fval('password') ?>" />
            
                    <?= ferr_span('password') ?>
                </li>
                
                <li>
                    <input type="submit" value="Autentificare" id="form_submit" class="button important" />
                    <a href="<?= url("reset_pass") ?>">Am uitat parola</a>
                </li>
            </ul>            
            <a href="<?= url('register') ?>">Inregistreaza-te!</a>
        </div>
    </form>
    <?php } ?>
    
</div>

<div id="content">

<?php
    // display flash message
    if (isset($_SESSION['_flash'])) { ?>

<div id="flash" class="flash <?= getattr($_SESSION, '_flash_class') ?>"><?= $_SESSION['_flash'] ?></div>

<?php
        // clear flash message 
        unset($_SESSION['_flash']);
        if (isset($_SESSION['_flash_class'])) {
            unset($_SESSION['_flash_class']);
        }
    }
//phpinfo();
?>
