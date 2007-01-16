<?php

require_once(IA_ROOT . 'www/format/table.php');
require_once(IA_ROOT . 'www/format/format.php');

include('header.php');

echo '<h1>'.htmlentities($view['title']).'</h1>';

if (count($view['users']) < 1) {
    print "<h3>Nici un utilizator inregistrat in aceasta runda</h3>";    
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

    $view['pager_style'] = 'standard';
    print format_table($view['users'], $column_infos, $view);
}

?>

<?php include('footer.php'); ?>
