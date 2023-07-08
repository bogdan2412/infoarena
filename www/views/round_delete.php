<?php
require_once 'header.php';
require_once(Config::ROOT . "www/format/table.php");
require_once(Config::ROOT . "www/format/format.php");

// Format the checkbox
function format_check($row) {
    // Nice trick to put the arguments in an array
    $id = html_escape('textblocks[]');
    $name = html_escape($row['name']);
    $html_checkbox = "<input type='checkbox' name='$id' value='$name'>";

    return $html_checkbox;
}

// Format the ID
function format_textblock_id($row) {
    $id = $row['id'];
    return html_escape("#$id");
}

// Format the name
function format_textblock_name($row) {
    global $page_name;

    $textblockurl = '<span id="link_'.html_escape($row['id']).'">';
    $textblockurl .= format_link(url_textblock($row['name']), $row['name']);
    $textblockurl .= '</span>';

    return $textblockurl;
}

$column_infos = array(
    array(
        'title' => '',
        'key' => 'ckeck',
        'rowform' => 'format_check'
    ),
    array(
        'title' => 'Număr',
        'key' => 'id',
        'rowform' => 'format_textblock_id'
    ),
    array(
        'title' => 'Nume',
        'key' => 'name',
        'rowform' => 'format_textblock_name'
    ),
);

?>

    <form method = 'post' action = ''>

    <h1>Paginile corelate cu runda <?= format_link(url_round_edit($view['round_id']), $view['round_id']); ?></h1>

    <?php
        $options = array(
            'display_entries' => $view['display_entries'],
            'total_entries' => $view['total_entries'],
            'first_entry' => $view['first_entry'],
            'pager_style' => 'standard',
            'show_count' => true,
            'show_display_entries' => true,
            'css_class' => 'alternating-colors fill-screen',
        );

        echo format_table($view['textblock_list'], $column_infos, $options);

        if ($view['total_entries']) {
    ?>
        <br><input type="submit" value="Șterge paginile" class="button" name="delete-pages" onclick="return confirm('Această acțiune este ireversibilă! Dorești să continui?');">
    <?php } ?>
    </form>

    <form method = 'post' action = ''>
    <input type="submit" value="Șterge runda, Forever..." class="button" name="delete-round" onclick="return confirm('Această acțiune este ireversibilă! Dorești să continui?');">
    </form>

<?php include('footer.php'); ?>
