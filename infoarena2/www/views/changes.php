<?php

require_once(IA_ROOT . "www/format/format.php");
include('header.php');

?>

    <h1>Ultimele modificari de pe infoarena</h1>

<a class="feed" href="<?= url('changes', array('format' => 'rss')) ?>" title="RSS Modificari" >RSS</a>

    <ul class="changes">
    <?php foreach ($revisions as $rev) { ?>
    <li>

<?php
$userlink = format_user_tiny($rev['user_name'], $rev['user_fullname']);
$pagelink = href(url($rev['name'], array(), true), $rev['title']);
$diffurl_params = array(
        'action' => 'diff',
        'rev_to' => $rev['revision_id'],
        'rev_from' => $rev['revision_id'] - 1,
);
$difflink = href(url($rev['name'], $diffurl_params, true), "diff");
$tstamp = date('j/n H:i', strtotime($rev['timestamp']));
echo "$tstamp: $userlink a modificat $pagelink ($difflink).";
?>
    </li>
    <?php } ?>
    </ul>

<?php include('footer.php'); ?>
