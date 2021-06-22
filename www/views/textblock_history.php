<?php
include(CUSTOM_THEME.'header.php');
require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "www/format/form.php");
?>

<h1>Istoria paginii <?= format_link(url_textblock($page_name), $page_name) ?></h1>

<?php
// Format links to a certain textblock revision.
function format_textblock_revision($row) {
    global $page_name;
    $rev_id = $row['revision_id'];
    return "#$rev_id";
}

function format_textblock_title($row) {
    global $page_name;
    $title = html_escape($row['title']);
    return "$title";
}

function format_ip($row) {
    if ($row['remote_ip_info'] && identity_can('textblock-view-ip', $row)) {
        return html_escape($row['remote_ip_info']);
    } else {
        return 'N/A';
    }
}

function format_operations($row) {
    global $page_name, $total_entries;
    $diffurl = url_textblock_diff($page_name, $row['revision_id'], $total_entries);
    $resturl = url_textblock_restore($page_name, $row['revision_id']);
    $delurl = url_textblock_delete_revision($page_name, $row['revision_id']);
    $viewurl = url_textblock_revision($page_name, $row['revision_id']);
    if ($row['revision_id'] == $total_entries) {
        $ret = '<strong>Ultima versiune</strong> ['. format_post_link($delurl, "Sterge") .']';
        return $ret;
    } else {
        return '['. format_link($diffurl, "Compară cu ultima revizie") .'] '.
                '['. format_post_link($resturl, "Înlocuiește") .'] '.
                '['. format_link($viewurl, "Vezi") .'] '.
                '['. format_post_link($delurl, "Șterge") .']';
    }
}

function format_compare($row) {
    global $total_entries;

    $button1_args = array(
        'name' => 'rev_from',
        'value' => $row['revision_id'],
    );
    if ($row['revision_id'] == $total_entries - 1) {
        $button1_args += array('checked' => 'checked');
    }

    $button2_args = array(
        'name' => 'rev_to',
        'value' => $row['revision_id'],
    );
    if ($row['revision_id'] == $total_entries) {
        $button2_args += array('checked' => 'checked');
    }

    return format_radio_button($button1_args) . " " .
        format_radio_button($button2_args);
}

$column_infos = array(
    array(
        'title' => 'Revizia',
        'key' => 'revision',
        'rowform' => 'format_textblock_revision'
    ),
    array(
        'title' => 'Titlu',
        'key' => 'title',
        'rowform' => 'format_textblock_title'
    ),
    array(
        'title' => 'Utilizator',
        'key' => 'username',
        'rowform' => function($row) {
            return format_user_tiny($row['user_name'], $row['user_fullname'], $row['rating_cache']);
        },
    ),
    array(
        'title' => 'Data',
        'key' => 'timestamp',
        'valform' => 'format_date',
    ),
    array(
        'title' => 'IP',
        'rowform' => 'format_ip',
    ),
    array(
        'title' => 'Operații',
        'rowform' => 'format_operations',
    ),

    array(
        'title' => 'Compară',
        'rowform' => 'format_compare',
        'css_class' => 'compare-radio',
    ),
);

$options = array(
    'css_class' => 'textblock-history',
    'display_entries' => $display_entries,
    'total_entries' => $total_entries,
    'first_entry' => $first_entry,
    'pager_style' => 'standard',
    'show_display_entries' => true,
    'show_count' => true,
    'surround_pages' => 3,
);

?>

<form
    action = "<?php echo html_escape(url_textblock_diff($page_name, null, null)); ?>"
    method = "get"
>

<?php
// We need to use a hidden field to pass "action=diff" as "get"
// because the form submit erases it from the url otherwise
?>
<input type = "hidden" name = "action" value = "diff" />
<div class="compare-button-container">
<input
    type = "submit"
    value = "Compară versiunile selectate"
    class = "button compare-button"
/>
</div>

<?php
$options["css_class"] = "fill-screen";
echo format_table($revisions, $column_infos, $options);
?>

<div class="compare-button-container">
<input
    type = "submit"
    value = "Compară versiunile selectate"
    class = "button compare-button"
/>
</div>
</form>

<?php include('footer.php'); ?>
