<?php

require_once(IA_ROOT_DIR."www/format/format.php");

// site-wide templates (templates shared with SMF / search)
//
// This file may be included from different environments
// (currently SMF and infoarena website) so it can use only
// limited application logic.

// display site header
function ia_template_header() {
    global $identity_user;
?>
<div id="header" class="clear">
    <?php if (!identity_is_anonymous()) { $username = $identity_user['username']; ?>
        <div id="userbox">
        <?= format_link(url_user_profile($username, true), format_user_avatar($username, "normal", true), false) ?>
            <div class="user">
                <strong><?= html_escape($identity_user['full_name']) ?></strong><br/>
                <?= format_user_ratingbadge($username, $identity_user['rating_cache']) ?>
                <?= format_link_access(url_user_profile($username, true), $username, 'p') ?><br/>
                <?= format_post_link(url_logout(), "logout", array(), true, array('class' => 'logout')) ?> |
                <?= format_link_access(url_account(), 'contul meu', 'c') ?>
            </div>
        </div>
    <?php } ?>
    <?php if (IA_DEVELOPMENT_MODE) { ?>
        <div id="dev_warning">
            Bravely working in development mode&hellip;<br/>Keep it up!
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
    <?php if (TOPNAV_ELEMENTS['blog']) { ?>
        <li>
            <?= getattr($pre, 'blog') ?>
            <?= format_link_access(url_blog(), "blog", 'b') ?>
            <?= getattr($post, 'blog') ?>
        </li>
    <?php } ?>
    <?php if (TOPNAV_ELEMENTS['forum']) { ?>
        <li>
            <?= getattr($pre, 'forum') ?>
            <?= format_link_access(url_forum(), "forum", 'f') ?>
            <?= getattr($post, 'forum') ?>
        </li>
    <?php } ?>
    <?php if (TOPNAV_ELEMENTS['calendar']) { ?>
        <li>
            <?= getattr($pre, 'calendar') ?>
            <?= format_link(url_forum() . "?action=calendar", "calendar") ?>
            <?= getattr($post, 'calendar') ?>
        </li>
    <?php } ?>
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

    <?php if (TOPNAV_ELEMENTS['messages']) { ?>
        <li>
            <?= getattr($pre, 'pm') ?>
	        <?php
                $new_pm_count = smf_get_pm_count($identity_user['username']);
                if ($new_pm_count) { ?>
                <?= format_link(url_forum() . "?action=pm", "<b>mesaje (".$new_pm_count.")</b>", false) ?>
	        <?php } else { ?>
                <?= format_link(url_forum() . "?action=pm", "mesaje") ?>
	        <?php } ?>
            <?= getattr($post, 'pm') ?>
        </li>
    <?php } ?>

    <?php if ($is_admin) { ?>
        <li>
            <?= getattr($pre, 'admin') ?>
            <?= format_link(url_admin(), 'admin') ?>
            <?= getattr($post, 'admin') ?>
        </li>
        <?php if (TOPNAV_ELEMENTS['forum']) { ?>
            <li>
                <?= getattr($pre, 'smf_admin') ?>
                <?= format_link(url_forum() . "?action=admin", "forum admin") ?>
                <?= getattr($post, 'smf_admin') ?>
            </li>
        <?php } ?>
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
    <!--Creative Commons License-->
        <a class="badge" rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/">
            <img
                alt="Creative Commons License"
                src="<?= url_static('images/CreativeCommonsBadge.png') ?>"/>
        </a>
        Cu excepția cazurilor în care se specifică altfel, conținutul
        site-ului <?= SITE_NAME ?><br/>este publicat sub licența
        <a rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/">Creative
        Commons Attribution-NonCommercial 2.5</a>.

        <!--/Creative Commons License-->
        <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
            <Work rdf:about="">
                <license rdf:resource="http://creativecommons.org/licenses/by-nc/2.5/" />
            </Work>
            <License rdf:about="http://creativecommons.org/licenses/by-nc/2.5/">
                <permits rdf:resource="http://web.resource.org/cc/Reproduction"/>
                <permits rdf:resource="http://web.resource.org/cc/Distribution"/>
                <requires rdf:resource="http://web.resource.org/cc/Notice"/>
                <requires rdf:resource="http://web.resource.org/cc/Attribution"/>
                <prohibits rdf:resource="http://web.resource.org/cc/CommercialUse"/>
                <permits rdf:resource="http://web.resource.org/cc/DerivativeWorks"/>
            </License>
        </rdf:RDF>
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
