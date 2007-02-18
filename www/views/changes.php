<?php

require_once(IA_ROOT_DIR . "www/format/format.php");
include('header.php');

?>

<h1>Ultimele modificari de pe infoarena</h1>

<a class="feed" href="<?= htmlentities(url_absolute(url_changes_rss())) ?>" title="RSS Modificari" >RSS</a>

<ul class="changes">
<?php foreach ($revisions as $rev) { ?>

    <li>

<?php
$userlink = format_user_tiny($rev['user_name'], $rev['user_fullname']);
$pagelink = format_link(url_absolute(url_textblock($rev['name'])), $rev['title']);
$diffurl_params = array(
        'action' => 'diff',
        'rev_to' => $rev['revision_id'],
        'rev_from' => $rev['revision_id'] - 1,
);
$difflink = format_link(url_textblock_diff(
            $rev['name'],
            $rev['revision_id'] - 1,
            $rev['revision_id']),
            "diff");
$tstamp = format_date($rev['timestamp']);
echo "$tstamp: $userlink a modificat $pagelink ($difflink).";
?>
    </li>
<?php } ?>
</ul>

<?php include('footer.php'); ?>
