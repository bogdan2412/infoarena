<?php

require_once(IA_ROOT_DIR . "www/format/format.php");
include('header.php');

?>

<h1>Ultimele modificari de pe infoarena</h1>

<a class="feed" href="<?= html_escape(url_absolute(url_changes_rss())) ?>" title="RSS Modificari" >RSS</a>

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
if (identity_can('textblock-view-ip', $rev) && $rev['remote_ip_info']) {
    $remote_ip = '('.$rev['remote_ip_info'].') ';
} else {
    $remote_ip = '';
}
echo "$tstamp: $userlink {$remote_ip}a modificat $pagelink ($difflink).";
?>
    </li>
<?php } ?>
</ul>

<?php include('footer.php'); ?>
