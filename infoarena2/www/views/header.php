<?php

require_once(IA_ROOT.'www/views/sitewide.php');
require_once(IA_ROOT.'www/views/utilities.php');

// Basic view checks.
log_assert(is_array($view));
log_assert(is_string($view['title']));

// Check forms.
if (isset($form_errors) || isset($form_values)) {
    log_assert(is_array($view['form_errors']));
    log_assert(is_array($view['form_values']));
    foreach ($form_errors as $k => $v) {
        if (!array_key_exists($k, $form_values)) {
            log_error("Form error $k with no form value.");
        }
    }
    foreach ($form_values as $k => $v) {
        if (htmlentities($k) != $k) {
            log_error("Form field $k contains special html chars.");
        }
    }
}
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?= htmlentities(getattr($view, 'title')) ?></title>

    <link type="text/css" rel="stylesheet" href="<?= htmlentities(url_static('css/sitewide.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= htmlentities(url_static('css/screen.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= htmlentities(url_static('css/tabber.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= htmlentities(url_static('css/SyntaxHighlighter.css')) ?>"/>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/config.js.php')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/MochiKit.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/default.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/tabber-minimized.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/submit.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/remotebox.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/sh/shCore.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/sh/shBrushCpp.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/sh/shBrushDelphi.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/sh/shBrushJava.js')) ?>"></script>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/sh/shInit.js')) ?>"></script>

    <?= getattr($view, 'head') ?>
</head>
<body<?= getattr($view, 'body_onload') ? ' onload="' . htmlentities(getattr($view, 'body_onload')) . '"' : '' ?>>

<div id="page">

<?php
if (!isset($topnav_select)) {
    $topnav_select = 'infoarena';
}
ia_template_header();
$smf_admin = ('admin' == getattr($identity_user, 'security_level'));
ia_template_topnav($topnav_select, $smf_admin);
?>

<div id="content_small" class="clear">
<div id="sidebar">
    <ul id="nav" class="clear">
        <li><a href="<?= htmlentities(url_home()) ?>">Home</a></li>
        <li><a href="<?= htmlentities(url_textblock('arhiva')) ?>">Arhiva de probleme</a></li>
        <li><a href="<?= htmlentities(url_textblock('concursuri')) ?>">Concursuri online</a></li>
        <li><a href="<?= htmlentities(url_textblock('clasament-rating')) ?>">Clasament</a></li>
        <li><a href="<?= htmlentities(url_textblock('articole')) ?>">Articole</a></li>
        <li><a href="<?= htmlentities(url_textblock('downloads')) ?>">Downloads</a></li>
        <li><a href="<?= htmlentities(url_textblock('links')) ?>">Links</a></li>
        <li><a href="<?= htmlentities(url_textblock('stiri')) ?>">Arhiva de stiri</a></li>
        <li><a href="<?= htmlentities(url_textblock('despre-infoarena')) ?>">Despre infoarena</a></li>
        <li><a href="<?= htmlentities(url_textblock('documentatie')) ?>">Documentatie</a></li>
        <li class="separator"><hr/></li>
        <li><a href="<?= htmlentities(url_textblock('monitor')) ?>">Monitorul de evaluare</a></li>
        <?php if (!identity_anonymous()) { ?>
        <li><a href="<?= htmlentities(url_submit()) ?>"><strong>Trimite solutii</strong></a></li>
        <li><a href="<?= htmlentities(url_account()) ?>">Contul meu</a></li>
        <?php } ?>
    </ul>

    <?php if (identity_anonymous()) { ?>
    <div id="login">
        <?php if (!isset($no_sidebar_login)) include(IA_ROOT.'www/views/form_login.php') ?>
        <p>
        <?= format_link(url_register(), "Ma inregistrez!" ) ?><br/>
        <?= format_link(url_resetpass(), "Mi-am uitat parola..." ) ?>
        </p>
    </div>
    <?php } ?>
    <p class="user-count"><?php echo user_count(); ?> membri inregistrati</p>
    <div id="srv_time" class="user-count" align="center"></div>
    <script type="text/javascript" src="<?= htmlentities(url_static('js/time.js')) ?>"></script>
    <script type="text/javascript">loadTime(<?= format_date(null, "%H, %M, %S");?>);</script>

    <?php include(IA_ROOT.'www/views/sidebar_ad.php'); ?>
</div>

<div id="main">
<?php

// breadcrumbs with recent pages
if (isset($recent_pages) && (1 < count($recent_pages))) {
    $bstring = '';
    foreach ($recent_pages as $rec_key => $rec_entry) {
        list($rec_url, $rec_title) = $rec_entry;

        $rec_title = htmlentities($rec_title);

        if ($bstring) {
            $bstring .= ' <span class="separator">|</span> ';
        }
        if ($current_url_key == $rec_key) {
            $bstring .= "<strong>{$rec_title}</strong>";
        }
        else {
            $bstring .= "<a href=\"" . htmlentities($rec_url) . "\">{$rec_title}</a>";
        }
    }
    echo '<p id="breadcrumbs">Pagini recente &raquo; ' . $bstring . '</p>';
}

?>

<?php
    // display flash message
    if (isset($_SESSION['_ia_flash'])) { ?>

<div id="flash" class="flash <?= htmlentities(getattr($_SESSION, '_ia_flash_class')) ?>"><?= htmlentities($_SESSION['_ia_flash']) ?></div>

<?php
        // clear flash message 
        unset($_SESSION['_ia_flash']);
        if (isset($_SESSION['_ia_flash_class'])) {
            unset($_SESSION['_ia_flash_class']);
        }
    }
?>
