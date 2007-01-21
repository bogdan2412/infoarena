<?php

require_once(IA_ROOT . 'www/format/table.php');
require_once(IA_ROOT . 'www/format/format.php');

include('header.php');

echo '<h1>'.htmlentities($view['title']).'</h1>';

if (count($view['users']) < 1) {
    echo "<p><strong>Nici un utilizator inscris la aceasta runda&hellip;</strong></p>";
}
else {
    $column_infos = array(
        array(
            'title' => 'Pozitie',
            'rowform' => create_function('$row', 'return $row["position"];'),
            'css_class' => 'number rank',
        ),
        array(
            'title' => 'Nume',
            'rowform' => create_function('$row',
                                         'return format_user_normal($row["username"], $row["fullname"], $row["rating"]);'),
        ),
        array(
            'title' => 'Rating',
            'rowform' => create_function('$row', 'return rating_scale($row["rating"]);'),
            'css_class' => 'number rating'
        ),
    );

    $options = array(
        'pager_style' => 'standard',
        'css_class'   => 'registered-users'
    );
    print format_table($view['users'], $column_infos, $options + $view);
}

?>

<?php include('footer.php'); ?>
