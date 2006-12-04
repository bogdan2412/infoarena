<?php

require_once(IA_ROOT."www/format/format.php");

// site-wide templates (templates shared with SMF / search)
//
// This file may be included from different environments
// (currently SMF and infoarena website) so it can use only
// limited application logic.

// display site header
function ia_template_header() {
?>
<div id="header" class="clear">
    <form id="search" action="get">
        <input type="text" id="inputbox" />
        <input type="submit" value="Cauta &raquo;"/>
    </form>
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
    <li><?= getattr($pre, 'calendar') ?><a href="<?= htmlentities(IA_SMF_URL) ?>?action=calendar">calendar competitii</a><?= getattr($post, 'calendar') ?></li>
<?php if (identity_anonymous()) { ?>
    <li><?= getattr($pre, 'login') . format_link(url('login', array(), true), "autentificare") . getattr($post, 'login') ?></li>
    <li><?= getattr($pre, 'register') . format_link(url('register', array(), true), "inregistare") . getattr($post, 'register') ?></li>
<?php } else { ?>
    <li><?= getattr($pre, 'profile') ?><a href="<?= htmlentities(url_user_profile($identity_user['username'], true)) ?>">profilul meu</a><?= getattr($post, 'profile') ?></li>
    <li><?= getattr($pre, 'pm') ?><a href="<?= htmlentities(IA_SMF_URL) ?>?action=pm">mesaje</a><?= getattr($post, 'pm') ?></li>
<?php if ($smf_admin) { ?>
    <li><?= getattr($pre, 'smf_admin') ?><a href="<?= htmlentities(IA_SMF_URL) ?>?action=admin">forum admin</a><?= getattr($post, 'smf_admin') ?></li>
<?php } ?>
    <li><?= getattr($pre, 'logout') ?><a href="<?= htmlentities(url('logout', array(), true)) ?>">inchide sesiunea</a><?= getattr($post, 'logout') ?></li>
<?php } ?>
</ul>
</div>

<?php
}

function ia_template_footer() {
?>
<div id="footer">
    <ul class="clear">
        <li><a href="<?= htmlentities(url('', array(), true)) ?>">Prima pagina</a></li>
        <li><a href="<?= htmlentities(url('Despre-infoarena', array(), true)) ?>">Despre infoarena</a></li>
        <li><a href="<?= htmlentities(url('Termeni-si-conditii', array(), true)) ?>">Termeni si conditii</a></li>
        <li><a href="<?= htmlentities(url('Contact', array(), true)) ?>">Contact</a></li>
        <li class="top"><a href="#header">Sari la inceputul paginii &uarr;</a></li>
    </ul>
    <p>&copy; 2006 - <a href="<?= htmlentities(url('Asociatia-infoarena', array(), true)) ?>">Asociatia infoarena</a></p>
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
