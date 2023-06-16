<?php

require_once(IA_ROOT_DIR . "www/format/format.php");
include(CUSTOM_THEME . 'header.php');

?>

<h1>Ultimele modificări de pe <?= SITE_NAME ?></h1>

<a class="feed" href="<?= html_escape(url_absolute(url_changes_rss())) ?>" title="RSS Modificari" >RSS</a>

<ul class="changes">
<?php foreach ($revisions as $rev) { ?>

    <li>

<?php
$created = ($rev["timestamp"] == $rev["creation_timestamp"]);
$userlink = format_user_tiny($rev['user_name'], $rev['user_fullname']);
$pagelink = format_link(url_textblock($rev['name'], true), "{$rev['title']} ({$rev['name']})");
$diffurl = url_textblock_diff($rev['name'], $rev['revision_id'] - 1, $rev['revision_id']);
$difflink = (!$created) ? " (".format_link($diffurl, "modificări").")" : "";
$tstamp = format_date($rev['timestamp']);
$created_or_changed = $created ? "creat" : "modificat";
if (identity_can('textblock-view-ip', $rev) && $rev['remote_ip_info']) {
    $remote_ip = '('.$rev['remote_ip_info'].') ';
} else {
    $remote_ip = '';
}
echo "$tstamp: $userlink $remote_ip a $created_or_changed $pagelink$difflink.";
?>
    </li>
<?php } ?>
</ul>

<?php include('footer.php'); ?>
