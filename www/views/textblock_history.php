<?php
include('header.php');
require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/format.php");
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
    $title = $row['title'];
    return "$title";
}

function format_operations($row)
{
    global $page_name, $total_entries;
    $diffurl = url_textblock_diff($page_name, $row['revision_id'], $total_entries);
    $resturl = url_textblock_restore($page_name, $row['revision_id']);
    $delurl = url_textblock_delete_revision($page_name, $row['revision_id']);
    $viewurl = url_textblock_revision($page_name, $row['revision_id']);
    if ($row['revision_id'] == $total_entries) {
        return '<strong>Ultima versiune</strong>';
    } else {
        return  '['. format_link($diffurl, "Compara") .'] '.
                '['. format_post_link($resturl, "Inlocuieste") .'] '.
                '['. format_link($viewurl, "Vezi") .'] '.
                '['. format_link($delurl, "Sterge") .']';
    }
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
        'rowform' => create_function_cached('$row',
                'return format_user_tiny($row["user_name"], $row["user_fullname"], $row["rating_cache"]);'),
    ),
    array(
        'title' => 'Data',
        'key' => 'timestamp',
        'valform' => 'format_date',
    ),

    array(
        'title' => 'Operatii',
        'rowform' => 'format_operations',
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

echo format_table($revisions, $column_infos, $options);

?>

<?php include('footer.php'); ?> 
