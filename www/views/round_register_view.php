<?php

require_once(IA_ROOT_DIR . 'www/format/table.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');

include(CUSTOM_THEME.'header.php');

echo '<h1>Utilizatori înregistrați la '.format_link(url_textblock($round['page_name']), $round['title']).'</h1>';

if (count($view['users']) < 1) {
    echo "<p><strong>Nici un utilizator înscris la această rundă&hellip;</strong></p>";
}
else {
    $column_infos = array(
        array(
            'title' => 'Poziție',
            'key' => 'position',
            'css_class' => 'number rank',
        ),
        array(
            'title' => 'Nume',
            'rowform' => function($row) {
                return format_user_normal($row['username'], $row['fullname'], $row['rating']);
            },
        ),
        array(
            'title' => 'Rating',
            'rowform' => function($row) {
                return rating_scale($row['rating']);
            },
            'css_class' => 'number rating'
        ),
    );

    $options = array(
        'pager_style' => 'standard',
        'show_count' => true,
        'css_class'   => 'registered-users'
    );
    print format_table($view['users'], $column_infos, $options + $view);
}

?>

<?php include('footer.php'); ?>
