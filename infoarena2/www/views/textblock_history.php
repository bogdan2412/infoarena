<?php
include('header.php');
require_once(IA_ROOT . "www/format/table.php");
require_once(IA_ROOT . "www/format/format.php");
?>

<h1>Istoria paginii <?= format_link(url($page_name), $page_name) ?></h1>

<?php
// Format links to a certain textblock revision.
function format_textblock_revision($row) {
    global $page_name;
    $rev_id = $row['revision_id'];
    $title = $row['title'];
    $url = url($page_name, array('revision' => $rev_id));
    return format_link($url, "#$rev_id: $title");
}

function format_operations($row)
{
    global $page_name, $total_entries;
    $diffurl = url_textblock_diff($page_name, $row['revision_id'], $total_entries);
    $resturl = url_textblock_restore($page_name, $row['revision_id']);
    if ($row['revision_id'] == $total_entries) {
        return '<strong>Ultima versiune</strong>';
    } else {
        return  '['. format_link($diffurl, "Compara") .']'.
                '['. format_link($resturl, "Incarca") .']';
    }
}

$column_infos = array(
    array(
        'title' => 'Revizia',
        'key' => 'title',
        'rowform' => 'format_textblock_revision'
    ),
    array(
        'title' => 'Utilizator',
        'key' => 'username',
        'rowform' => create_function('$row',
                                     'return format_user_tiny($row["user_name"], $row["user_fullname"]);'),
    ),
    array(
        'title' => 'Data',
        'key' => 'timestamp',
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
    'surround_pages' => 3,
);

echo format_table($revisions, $column_infos, $options);

?>

<?php include('footer.php'); ?>
