<?php

require_once(Config::ROOT."www/format/format.php");

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
    <?php if (Config::DEVELOPMENT_MODE) { ?>
        <div id="dev_warning">
            Bravely working in development mode&hellip;<br>Keep it up!
        </div>
    <?php } ?>

    <h1><?= format_link(url_home(), Config::SITE_NAME . ' informatică de performanță') ?></h1>
</div>
<?php
}

function ia_template_footer() {
?>
<div id="footer">
    <ul class="clear">
        <li class="copyright">
            ©
            <?= Config::COPYRIGHT_FIRST_YEAR . '-' . date("Y") ?>
            <?= Config::COPYRIGHT_OWNER ?>
        </li>
        <li class="separate"><?= format_link(url_home(), "Prima pagină") ?></li>
        <li><?= format_link(url_textblock(Config::ABOUT_PAGE), 'Despre ' . Config::SITE_NAME) ?></li>
        <li><?= format_link(url_textblock("termeni-si-conditii"), "Termeni și condiții") ?></li>
        <li><?= format_link(url_textblock("contact"), "Contact") ?></li>
        <li class="top"><a href="#header">Sari la începutul paginii &uarr;</a></li>
    </ul>
<?php if (!Config::DEVELOPMENT_MODE) { ?>
    <p class="cc">
        <a class="badge" rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">
            <img
                alt="Creative Commons License"
                src="<?= url_static('images/creative-commons.png') ?>">
        </a>
        Cu excepția cazurilor în care se specifică altfel, conținutul
        site-ului <?= Config::SITE_NAME ?><br>este publicat sub licența
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

<?php
}
?>
