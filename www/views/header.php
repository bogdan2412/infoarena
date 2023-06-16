<?php

require_once(IA_ROOT_DIR.'www/views/sitewide.php');
require_once(IA_ROOT_DIR.'www/views/utilities.php');
require_once(IA_ROOT_DIR.'www/macros/macro_calendar.php');

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

header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
    // Hack to insert meta description and keywords for home page.
    // Description should get us a better snippet for keywords that hit our
    // home page. If this proves to be effective we should find a way to
    // include meta descriptions for the most visited pages (can we do it with
    // a macro?).
    if (getattr($view, 'page_name') == "home") {
?>
    <meta name="Description" content="Concursuri de programare, Știri și articole de informatică, Comunitate online. Arhivă de probleme, Evaluare 24/24, Forum, Resurse educaționale, Pregătire pentru olimpiadă." />
    <meta name="keywords" content="Concursuri, Informatică, Programare, Comunitate, Algoritmi, Articole, Evaluare, Pregătire" />
    <meta name="verify-v1" content="j9UCDYvsDL2pLtkJDDkE4HnHVmXakgvz30vOyIJ+6cI=" />
    <meta name="verify-v1" content="ac36ilLp6y4xp71sJgZEjFMgow6YCLkyQLrG/b2iT/Q=" />
    <meta name="verify-v1" content="PQDTb5Advw297iADXVUYuxh0z+iQKfq1wtYWnfHCb1Y=" />
    <meta name="verify-v1" content="GUKPcDm0TzwAR7r5xbagq576p/YWH9su/Ca4o8YlYBg=" /> <!--bogdan2412-->
    <meta name="google-site-verification" content="T_HFYNIO3S70696yFC5rkbPn339gjnfVwMqYPmU866I" /> <!--devilkind-->
<?php
    }

    // Add aditional meta data for other pages such as blog posts
    $meta_info = getattr($view, 'meta_info', array());
    foreach ($meta_info as $tag) {
        echo format_tag('meta', null, $tag);
    }
?>

    <title><?= html_escape(getattr($view, 'title')) ?></title>

    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/sitewide.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/iconize.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/screen.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/tabber.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/highlight-theme.css')) ?>"/>
    <link type="text/css" rel="stylesheet" href="<?= html_escape(url_static('css/print.css')) ?>" media="print" />
    <link rel="icon" href="<?= IA_URL."favicon.ico" ?>" type="image/vnd.microsoft.icon" />
    <script type="text/javascript" src="<?= html_escape(url_static('js/config.js.php')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(IA_DEVELOPMENT_MODE?url_static('js/jquery-1.7.2.js'):'//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js') ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/default.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/tabber-minimized.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/submit.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/remotebox.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/postdata.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/highlight.pack.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/highlight-line-numbers.min.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/tags.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/roundtimer.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/restoreparity.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/foreach.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/sorttable.js')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/tablednd.js')) ?>"></script>

    <script type="text/javascript">
        hljs.initHighlightingOnLoad();
        hljs.initLineNumbersOnLoad();
    </script>
<?php
    if (request("action") == 'rating') {
?>
    <script type="text/javascript" src="<?= html_escape(url_static('js/swfobject.js')) ?>"></script>
<?php
    }
?>

    <?= getattr($view, 'head') ?>
</head>
<body id="infoarena">
<div id="page">

<?php
if (!isset($topnav_select)) {
    $topnav_select = SITE_NAME;
}
ia_template_header();
$is_admin = ('admin' == getattr($identity_user, 'security_level'));
ia_template_topnav($topnav_select, $is_admin);
?>

<div id="content_small" class="clear">
<div id="sidebar">
    <ul id="nav" class="clear">
        <li><a href="<?= html_escape(url_home()) ?>">Home</a></li>
        <?php if (SIDEBAR_ELEMENTS['archives']) { ?>
            <li><?= format_link_access(url_textblock('arhiva'), "Arhiva de probleme", 'a') ?></li>
            <li><a href="<?= html_escape(url_textblock('arhiva-educationala')) ?>">Arhiva educațională</a></li>
            <li><a href="<?= html_escape(url_textblock('arhiva-monthly')) ?>">Arhiva monthly</a></li>
        <?php } ?>
        <li><a href="<?= html_escape(url_textblock('concursuri')) ?>">Concursuri</a></li>
        <li><a href="<?= html_escape(url_textblock('concursuri-virtuale')) ?>">Concursuri virtuale</a></li>
        <li><a href="<?= html_escape(url_textblock('clasament-rating')) ?>">Clasament</a></li>
        <?php if (SIDEBAR_ELEMENTS['task-search']) { ?>
            <li><a href="<?= url_task_search([]) ?>">Caută probleme</a></li>
        <?php } ?>
        <?php if (SIDEBAR_ELEMENTS['articles']) { ?>
            <li><a href="<?= html_escape(url_textblock('articole')) ?>">Articole</a></li>
        <?php } ?>
        <?php if (SIDEBAR_ELEMENTS['downloads']) { ?>
            <li><a href="<?= html_escape(url_textblock('downloads')) ?>">Downloads</a></li>
        <?php } ?>
        <?php if (SIDEBAR_ELEMENTS['links']) { ?>
            <li><a href="<?= html_escape(url_textblock('links')) ?>">Links</a></li>
        <?php } ?>
        <?php if (SIDEBAR_ELEMENTS['docs']) { ?>
            <li><a href="<?= html_escape(url_textblock('documentatie')) ?>">Documentație</a></li>
        <?php } ?>
        <?php if (SIDEBAR_ELEMENTS['about']) { ?>
            <li><a href="<?= html_escape(url_textblock(ABOUT_PAGE)) ?>">Despre <?= SITE_NAME ?></a></li>
        <?php } ?>
        <li class="separator"><hr/></li>
        <li><?= format_link_access(url_monitor(array('user' => identity_get_username())), "Monitorul de evaluare", 'm') ?></li>
        <?php if (!identity_is_anonymous()) { ?>
            <li><a href="<?= html_escape(url_submit()) ?>"><strong>Trimite soluții</strong></a></li>
            <li><?= format_link_access(url_account(), "Contul meu", 'c') ?></li>
        <?php } ?>
    </ul>

    <?php if (GOOGLE_SEARCH && !IA_DEVELOPMENT_MODE) { ?>
        <div id="google-search">
            <?php include(IA_ROOT_DIR.'www/views/google_search.php'); ?>
        </div>
    <?php } ?>

    <?php if (SIDEBAR_ELEMENTS['calendar']) { ?>
        <div id="calendar">
            <?= macro_calendar(array()) ?>
        </div>
    <?php } ?>

    <?php if (identity_is_anonymous()) { ?>
    <div id="login">
        <?php if (!isset($no_sidebar_login)) include(IA_ROOT_DIR.'www/views/form_login.php') ?>
        <p>
        <?= format_link(url_register(), "Mă înregistrez!" ) ?><br/>
        <?= format_link(url_resetpass(), "Mi-am uitat parola..." ) ?>
        </p>
    </div>
    <?php } ?>
    <p class="user-count"><?php echo user_count(); ?> membri înregistrați</p>
    <div id="srv_time" class="user-count" align="center"></div>
    <script type="text/javascript" src="<?= html_escape(url_static('js/time.js')) ?>"></script>
    <script type="text/javascript">loadTime(<?= format_date(null, 'HH, mm, ss');?>);</script>

    <?php if (SIDEBAR_ELEMENTS['ad']) {
        include(IA_ROOT_DIR.'www/views/sidebar_ad.php');
        } ?>
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
    // display flash message
    if (isset($_SESSION['_ia_flash'])) { ?>

<div id="flash" class="flash <?= html_escape(getattr($_SESSION, '_ia_flash_class')) ?>"><?= html_escape($_SESSION['_ia_flash']) ?></div>

<?php
        // clear flash message
        unset($_SESSION['_ia_flash']);
        if (isset($_SESSION['_ia_flash_class'])) {
            unset($_SESSION['_ia_flash_class']);
        }
    }
?>
