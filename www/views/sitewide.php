<?php

require_once(IA_ROOT_DIR."www/format/format.php");

// site-wide templates (templates shared with search)
//
// This file may be included from different environments
// (currently infoarena website) so it can use only
// limited application logic.

// display site header
  function ia_template_header() {
    // FIXME: Keep this in sync with template/bits/header.tpl while they coexist.
    global $identity_user;
?>
<div id="header" class="clear">
    <?php if (!identity_is_anonymous()) { $username = $identity_user['username']; ?>
        <div id="userbox">
        <?= format_link(url_user_profile($username, true), format_user_avatar($username, "normal", true), false) ?>
            <div class="user">
                <strong><?= html_escape($identity_user['full_name']) ?></strong><br>
                <?= format_user_ratingbadge($username, $identity_user['rating_cache']) ?>
                <?= format_link_access(url_user_profile($username, true), $username, 'p') ?><br>
                <?= format_post_link(url_logout(), "logout", array(), true, array('class' => 'logout')) ?> |
                <?= format_link_access(url_account(), 'contul meu', 'c') ?>
            </div>
        </div>
    <?php } ?>
    <?php if (IA_DEVELOPMENT_MODE) { ?>
        <div id="dev_warning">
            Bravely working in development mode&hellip;<br>Keep it up!
        </div>
    <?php } ?>

    <h1><?= format_link(url_home(), SITE_NAME . ' informatică de performanță') ?></h1>
</div>
<?php
}

// display main navigation bar
function ia_template_topnav($selected = SITE_NAME, $is_admin = false) {
    global $identity_user;

    $pre = array($selected => '<strong>');
    $post = array($selected => '</strong>');
?>
<div id="topnav">
<ul>
    <li>
        <?= getattr($pre, SITE_NAME) ?>
        <?= format_link(url_home(), NAV_HOMEPAGE_TEXT, false) ?>
        <?= getattr($post, SITE_NAME) ?>
    </li>
<?php if (identity_is_anonymous()) { ?>
    <li>
        <?= getattr($pre, 'login')?>
        <?= format_link(url_login(), "autentificare") ?>
        <?= getattr($post, 'login') ?>
    </li>
    <li>
        <?= getattr($pre, 'register')?>
        <?= format_link(url_register(), "înregistrare") ?>
        <?= getattr($post, 'register') ?>
    </li>
<?php } else { ?>
    <li>
        <?= getattr($pre, 'profile') ?>
        <?= format_link_access(url_user_profile($identity_user['username']), 'profilul meu', 'p') ?>
        <?= getattr($post, 'profile') ?></li>

    <?php if ($is_admin) { ?>
        <li>
            <?= getattr($pre, 'admin') ?>
            <?= format_link(url_admin(), 'admin') ?>
            <?= getattr($post, 'admin') ?>
        </li>
    <?php } ?>
<?php } ?>
</ul>
</div>

<?php
}

function ia_template_footer() {
?>
<div id="footer">
    <ul class="clear">
        <li class="copyright">
            &copy;
            <?= COPYRIGHT_FIRST_YEAR . '-' . date("Y") ?>
            <?php if (COPYRIGHT_OWNER_PAGE): ?>
                <?= format_link(url_textblock(COPYRIGHT_OWNER_PAGE), COPYRIGHT_OWNER) ?>
            <?php else: ?>
                <?= COPYRIGHT_OWNER ?>
            <?php endif; ?>
        </li>
        <li class="separate"><?= format_link(url_home(), "Prima pagină") ?></li>
        <li><?= format_link(url_textblock(ABOUT_PAGE), 'Despre ' . SITE_NAME) ?></li>
        <li><?= format_link(url_textblock("termeni-si-conditii"), "Termeni și condiții") ?></li>
        <li><?= format_link(url_textblock("contact"), "Contact") ?></li>
        <li class="top"><a href="#header">Sari la începutul paginii &uarr;</a></li>
    </ul>
<?php if (!IA_DEVELOPMENT_MODE) { ?>
    <p class="cc">
        <a class="badge" rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">
            <img
                alt="Creative Commons License"
                src="<?= url_static('images/creative-commons.png') ?>">
        </a>
        Cu excepția cazurilor în care se specifică altfel, conținutul
        site-ului <?= SITE_NAME ?><br>este publicat sub licența
        <a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">Creative
        Commons Attribution-NonCommercial-ShareAlike 4.0 International</a>.
    </p>
<?php
    }
    else {
        // Development mode: display current page's log in site footer
        global $execution_stats;
        log_execution_stats();
        $buffer = $execution_stats['log_copy'];
        echo '<textarea id="log" rows="50" cols="80">';
        echo html_escape($buffer);
        echo '</textarea>';
    }
?>
</div>

<?php if (!IA_DEVELOPMENT_MODE && GOOGLE_ANALYTICS_TRACKING_ID) { ?>
    <script src="http://www.google-analytics.com/urchin.js">
    </script>
    <script>
    _uacct = "<?php echo GOOGLE_ANALYTICS_TRACKING_ID; ?>";
    _udn = "infoarena.ro";
    urchinTracker();
    </script>
<?php } ?>

<?php
}

?>
