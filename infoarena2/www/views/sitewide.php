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
    <!-- Search feature not ready yet
    <form id="search" action="get">
        <input type="text" id="inputbox" />
        <input type="submit" value="Cauta &raquo;"/>
    </form>
    -->
    <?php if (!identity_anonymous()) { $username = $identity_user['username']; ?>
        <div id="userbox">
        <?= format_link(url_user_profile($username, true), format_user_avatar($username, 50, 50, true), false) ?>
            <span class="user">
                <strong><?= htmlentities($identity_user['full_name']) ?></strong><br/>
                <?= format_user_ratingbadge($username, $identity_user['rating_cache']) ?>
                <?= format_link(url_user_profile($username, true), $username) ?><br/>
                <?= format_link(url_logout(), "logout", true, array('class' => 'logout')) ?> |
                <?= format_link(url_account(), 'contul meu') ?>
            </span>
        </div>
    <?php } ?>

    <?php if (IA_DEVELOPMENT_MODE) { ?>
        <div id="dev_warning">
            Bravely working in development mode&hellip;<br/>Keep it up!
        </div>
    <?php } ?>

    <h1><?= format_link(url_home(), "infoarena informatica de performanta") ?></h1>
</div>
<?php
}

// display main navigation bar
function ia_template_topnav($selected = 'infoarena', $smf_admin = false) {
    global $identity_user;

    $pre = array($selected => '<strong>');
    $post = array($selected => '</strong>');
?>
<div id="topnav">
<ul>
    <li>
        <?= getattr($pre, 'infoarena') ?>
        <?= format_link(url_home(), 'info<em>arena</em>', false) ?>
        <?= getattr($post, 'infoarena') ?>
    </li>
    <li>
        <?= getattr($pre, 'forum') ?>
        <?= format_link(url_forum(), "forum") ?>
        <?= getattr($post, 'forum') ?>
    </li>
    <li>
        <?= getattr($pre, 'calendar') ?>
        <?= format_link(url_forum() . "?action=calendar", "calendar") ?>
        <?= getattr($post, 'calendar') ?>
    </li>
<?php if (identity_anonymous()) { ?>
    <li>
        <?= getattr($pre, 'login')?>
        <?= format_link(url_login(), "autentificare") ?>
        <?= getattr($post, 'login') ?>
    </li>
    <li>
        <?= getattr($pre, 'register')?>
        <?= format_link(url_login(), "inregistare") ?>
        <?= getattr($post, 'register') ?>
    </li>
<?php } else { ?>
    <li>
        <?= getattr($pre, 'profile') ?>
        <?= format_link(url_user_profile($identity_user['username']), 'profilul meu') ?>
        <?= getattr($post, 'profile') ?></li>
    <li>
        <?= getattr($pre, 'pm') ?>
        <?= format_link(url_forum() . "?action=pm", "mesaje") ?>
        <?= getattr($post, 'pm') ?>
    </li>
<?php if ($smf_admin) { ?>
    <li>
        <?= getattr($pre, 'smf_admin') ?>
        <?= format_link(url_forum() . "?action=admin", "forum admin") ?>
        <?= getattr($post, 'smf_admin') ?>
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
        <li>&copy;&nbsp;2004-<?= date("Y") ?>&nbsp;<?= format_link(url_textblock('Asociatia-infoarena'), "Asociatia infoarena") ?></li>
        <li class="separate"><?= format_link(url_home(), "Prima pagina") ?></li>
        <li><?= format_link(url_textblock("despre-infoarena"), "Despre infoarena") ?></li>
        <li><?= format_link(url_textblock("termeni-si-conditii"), "Termeni si conditii") ?></li>
        <li><?= format_link(url_textblock("contact"), "Contact") ?></li>
        <li class="top"><a href="#header">Sari la inceputul paginii &uarr;</a></li>
    </ul>
<?php if (!IA_DEVELOPMENT_MODE) { ?>
    <p class="cc">
    <!--Creative Commons License-->
    <a class="badge" rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/"><img alt="Creative Commons License" src="http://i.creativecommons.org/l/by-nc/2.5/88x31.png"/></a>
    Cu exceptia cazurilor in care se specifica altfel, continutul site-ului infoarena<br/>este publicat sub licenta <a rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/">Creative Commons Attribution-NonCommercial 2.5</a>.
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
        if (defined("IA_FROM_SMF")) {
            // too bad SMF cannot link infoarena log API :(
        }
        else {
            global $execution_stats;
            log_execution_stats();
            $buffer = $execution_stats['log_copy'];
            echo '<textarea id="log" rows="30" cols="60">';
            echo htmlentities($buffer);
            echo '</textarea>';
        }
    }
?>
</div>

<?php if (!IA_DEVELOPMENT_MODE) { ?>
    <script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
    </script>
    <script type="text/javascript">
    _uacct = "UA-113289-8";
    _udn = "infoarena.ro";
    urchinTracker();
    </script>
<?php } ?>

<?php
}

?>
