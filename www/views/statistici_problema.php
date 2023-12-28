<?php

require_once Config::ROOT.'www/format/format.php';
require_once Config::ROOT.'www/format/table.php';

require_once 'header.php';

$username = '';
if (array_key_exists('username', $view)) {
    $username = $view['username'];
}
$task_id = $view['task_id'];
$round_id = $view['round_id'];
$round_name = $view['round_name'];

echo '<h1>Statisticile problemei '.format_link($view['task_url'], $view['task_id']).
     ' ('.htmlentities($round_name).')</h1>';

$data = $view['data'];

echo '<h2>Clasamente</h2>';
if (count($data['time']) === 0) {
    echo 'Nicio sursă corectă trimisă la această problemă :(';
} else {
    $column_infos = array(
        array(
            'title' => 'Loc',
            'key' => 'position',
            'css_class' => 'number',
        ),
        array(
            'title' => 'Utilizator',
            'key' => 'username',
            'rowform' => function($row) {
                return format_user_tiny($row['username']);
            },
        ),
        array(
            'title' => '',
            'key' => 'special_score',
            'css_class' => 'number',
            'rowform' => function($row) {
                return format_link('job_detail/'.$row['job_id'], $row['special_score']);
            },
        ),
    );

    $options = [];

    $long_wording = array(
        'time' => 'timpul de execuție',
        'memory' => 'memoria folosită',
        'size' => 'dimensiunea sursei',
    );
    $header_wording = array(
        'time' => 'Timp',
        'memory' => 'Memorie',
        'size' => 'Mărime',
    );

    foreach ($data as $criteria => $ranking) {
        echo 'Clasament după '.$long_wording[$criteria];
        $column_infos[count($column_infos) - 1]['title'] = $header_wording[$criteria];
        echo format_table($data[$criteria], $column_infos, $options);
        echo '<br>';
    }
}

echo '<h2>Alte statistici</h2>';
echo 'Numărul mediu de submisii greșite: '.$view['average_wrong_submissions'].'<br>';
if (Identity::isLoggedIn()) {
    echo 'Numărul tău de submisii greșite: '.$view['user_wrong_submissions'].'<br>';
}
echo 'Procentajul de reușită: '.$view['solved_percentage'].'%<br>';

include 'footer.php';
