<?php

require_once(Config::ROOT.'www/views/sitewide.php');
require_once(Config::ROOT.'www/views/utilities.php');

// Basic view checks.
log_assert(is_array($view));
log_assert(is_string($view['title']));

// Check forms.
if (isset($form_errors) || isset($form_values)) {
    log_assert(is_array($view['form_errors']));
    log_assert(is_array($view['form_values']));
    foreach ($form_values as $k => $v) {
        if (html_escape($k) != $k) {
            log_error("Form field $k contains special html chars.");
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
<?php
    // Hack to insert meta description and keywords for home page.
    // Description should get us a better snippet for keywords that hit our
    // home page. If this proves to be effective we should find a way to
    // include meta descriptions for the most visited pages (can we do it with
    // a macro?).
    if (getattr($view, 'page_name') == "home") {
?>
    <meta name="Description" content="Concursuri de programare, Comunitate online. Arhivă de probleme, Evaluare 24/24, Resurse educaționale, Pregătire pentru olimpiadă.">
    <meta name="keywords" content="Concursuri, Informatică, Programare, Comunitate, Algoritmi, Articole, Evaluare, Pregătire">
<?php
    }
?>

    <title><?= html_escape(getattr($view, 'title')) ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400;0,500;1,400;1,500&amp;family=Ubuntu:ital,wght@0,300;0,400;1,300;1,400&amp;display=swap" rel="stylesheet">

    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/sitewide.css')) ?>">
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/third-party/iconize-0.5/iconize.css')) ?>">
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/screen.css')) ?>">
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/third-party/tabber.css')) ?>">
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/third-party/highlight-theme.css')) ?>">
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/print.css')) ?>">

    <link
      href="<?= Config::URL_HOST . Config::URL_PREFIX."static/images/favicon.svg" ?>"
      rel="icon"
      type="image/svg+xml">
    <script src="<?= html_escape(url_static('js/config.js.php')) ?>"></script>
    <script src="<?= html_escape(Config::DEVELOPMENT_MODE?url_static('js/third-party/jquery-3.7.0.min.js'):'//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js') ?>"></script>
    <script src="<?= html_escape(url_static('js/default.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/third-party/tabber-minimized.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/submit.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/postdata.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/third-party/highlight.pack.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/third-party/highlight-line-numbers.min.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/tags.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/roundtimer.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/third-party/foreach.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/third-party/sorttable.js')) ?>"></script>
    <script src="<?= html_escape(url_static('js/third-party/tablednd.js')) ?>"></script>

    <script>
        hljs.initHighlightingOnLoad();
        hljs.initLineNumbersOnLoad();
    </script>

    <?= getattr($view, 'head') ?>
</head>
<body id="infoarena">
<div id="page">

<?php
ia_template_header();
$is_admin = ('admin' == getattr($identity_user, 'security_level'));
?>

<div id="content_small" class="clear">
<div id="sidebar">
    <ul id="nav" class="clear">
        <li><a href="<?= html_escape(url_home()) ?>">Home</a></li>
        <li><a href="<?= html_escape(url_textblock('concursuri')) ?>">Concursuri</a></li>
        <li><a href="<?= html_escape(url_textblock('concursuri-virtuale')) ?>">Concursuri virtuale</a></li>
        <li><a href="<?= html_escape(url_textblock('clasament-rating')) ?>">Clasament</a></li>
		<li><?= format_link_access(url_monitor(array('user' => identity_get_username())), "Monitorul de evaluare", 'm') ?></li>
        <li class="separator"><hr></li>
        <li><a href="<?= url_task_search([]) ?>">Categorii probleme</a></li>

        <?php if (Config::GOOGLE_CSE_TOKEN && !Config::DEVELOPMENT_MODE) { ?>
		    <li><a href="<?= html_escape(url_google_search()) ?>">Căutare probleme</a></li>
		    <div id="google-search">
			    <?php include(Config::ROOT.'www/views/google_search.php'); ?>
		    </div>
        <?php } ?>

        <li class="separator"><hr></li>
        <?php if (!identity_is_anonymous()) { ?>
            <li><a href="<?= html_escape(url_submit()) ?>"><strong>Trimite soluții</strong></a></li>
            <li><?= format_link_access(url_account(), "Contul meu", 'c') ?></li>
			<!-- mihai adaugare elemente top-menu la sidebar -->
			<li><?= format_link_access(url_user_profile($identity_user['username']), 'Profilul meu', 'p') ?></li>
			<?php if ($is_admin) { ?>
			<li class="separator"><hr></li>
			<li><?= format_link_access(url_admin(), 'Administrativ', 'a') ?></li>
			<?php } ?>
			<!-- end adaugare elemente sidebar-->
        <?php } ?>

    </ul>

    <?php if (identity_is_anonymous()) { ?>
    <div id="login">
        <?php if (!isset($no_sidebar_login)) Smart::displayBit('auth/loginForm.tpl'); ?>
        <p>
        <?= format_link(url_register(), "Mă înregistrez!" ) ?><br>
        <?= format_link(url_resetpass(), "Mi-am uitat parola..." ) ?>
        </p>
    </div>
    <?php } ?>
    <p class="user-count"><?php echo user_count(); ?> membri înregistrați</p>
    <div id="srv_time" class="user-count"></div>
    <script src="<?= html_escape(url_static('js/time.js')) ?>"></script>
    <script>loadTime(<?= format_date(null, 'HH, mm, ss');?>);</script>
</div>

<div id="main">
<?php

// breadcrumbs with recent pages
if (isset($recent_pages) && (1 < count($recent_pages))) {
    $bstring = '';
    foreach ($recent_pages as $rec_key => $rec_entry) {
        list($rec_url, $rec_title) = $rec_entry;

        $rec_title = html_escape($rec_title);

        if ($bstring) {
            $bstring .= ' <span class="separator">|</span> ';
        }
        if ($current_url_key == $rec_key) {
            $bstring .= "<strong>{$rec_title}</strong>";
        }
        else {
            $bstring .= "<a href=\"" . html_escape($rec_url) . "\">{$rec_title}</a>";
        }
    }
    echo '<p id="breadcrumbs">Pagini recente &raquo; ' . $bstring . '</p>';
}

?>

<?php
  Smart::displayBit('layout/flashMessages.tpl');
?>
