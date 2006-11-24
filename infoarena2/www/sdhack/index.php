<?php

// sub-domain hack. Forward requests to files only reachable in other
// subdomains. Comply to browser security standards.
//
// Modern browsers refuse to process XmlHttpRequest's or iframes with src
// outside the current domain/subdomain.
// For example, a wiki page served in infoarena.ro cannot do a XmlHttpRequest
// to forum.infoarena.ro/ia_lastposts.php
//
// FIXME: Couldn't find a working & reliable solution with mod_rewrite.
// RewriteRule will force a client redirect, not accepted by browser.

$root = '../../';

if (isset($_GET['file'])) {
    $file = $_GET['file'];
}
else {
    $file = null;
}

if ('ia_recentposts' == $file) {
    chdir($root.'smf');
    require('ia_recentposts.php');
}
else if ('ia_recenttopics' == $file) {
    chdir($root.'smf');
    require('ia_recenttopics.php');
}
else {
    echo 'Nothing to do';
    die();
}

?>
