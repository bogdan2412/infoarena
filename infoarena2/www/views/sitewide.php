<?php

require_once(IA_ROOT."www/format/format.php");

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
                <?= format_user_ratingbadge($username, $identity_user['rating_cache']).format_link(url_user_profile($username, true), $username) ?><br/>
                <a href="<?= htmlentities(url('logout', array(), true)) ?>" class="logout">logout</a> | <?= format_link(url('account', array(), true), 'contul meu') ?>
            </span>
        </div>
    <?php } ?>

    <h1><a title="informatica de performanta" href="<?= htmlentities(url('', array(), true)) ?>">infoarena,
        informatica de performanta</a></h1>
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
    <li><?= getattr($pre, 'infoarena') ?><a href="<?= htmlentities(url('', array(), true)) ?>">info<em>arena</em></a><?= getattr($post, 'infoarena') ?></li>
    <li><?= getattr($pre, 'forum') ?><a href="<?= htmlentities(IA_SMF_URL) ?>">forum</a><?= getattr($post, 'forum') ?></li>
    <li><?= getattr($pre, 'calendar') ?><a href="<?= htmlentities(IA_SMF_URL) ?>?action=calendar">calendar</a><?= getattr($post, 'calendar') ?></li>
<?php if (identity_anonymous()) { ?>
    <li><?= getattr($pre, 'login') . format_link(url('login', array(), true), "autentificare") . getattr($post, 'login') ?></li>
    <li><?= getattr($pre, 'register') . format_link(url('register', array(), true), "inregistare") . getattr($post, 'register') ?></li>
<?php } else { ?>
    <li><?= getattr($pre, 'profile') ?><a href="<?= htmlentities(url_user_profile($identity_user['username'], true)) ?>">profilul meu</a><?= getattr($post, 'profile') ?></li>
    <li><?= getattr($pre, 'pm') ?><a href="<?= htmlentities(IA_SMF_URL) ?>?action=pm">mesaje</a><?= getattr($post, 'pm') ?></li>
<?php if ($smf_admin) { ?>
    <li><?= getattr($pre, 'smf_admin') ?><a href="<?= htmlentities(IA_SMF_URL) ?>?action=admin">forum admin</a><?= getattr($post, 'smf_admin') ?></li>
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
        <li>&copy;&nbsp;2006&nbsp;-&nbsp;<a href="<?= htmlentities(url('Asociatia-infoarena', array(), true)) ?>">Asociatia infoarena</a></li>
        <li class="separate"><a href="<?= htmlentities(url('', array(), true)) ?>">Prima pagina</a></li>
        <li><a href="<?= htmlentities(url('Despre-infoarena', array(), true)) ?>">Despre infoarena</a></li>
        <li><a href="<?= htmlentities(url('Termeni-si-conditii', array(), true)) ?>">Termeni si conditii</a></li>
        <li><a href="<?= htmlentities(url('Contact', array(), true)) ?>">Contact</a></li>
        <li class="top"><a href="#header">Sari la inceputul paginii &uarr;</a></li>
    </ul>
    <p class="cc">
    <!--Creative Commons License-->
    <a class="badge" rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/"><img alt="Creative Commons License" src="http://i.creativecommons.org/l/by-nc/2.5/88x31.png"/></a>
    Cu exceptia cazurilor in care se specifica altfel, continutul site-ului infoarena<br/>este publicat sub licenta <a rel="license" href="http://creativecommons.org/licenses/by-nc/2.5/">Creative Commons Attribution-NonCommercial 2.5</a>.
    <!--/Creative Commons License-->
    <!-- <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
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
    </rdf:RDF> -->    
    
    </p>
</div>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-113289-8";
_udn = "infoarena.ro";
urchinTracker();
</script>

<?php
}

?>
