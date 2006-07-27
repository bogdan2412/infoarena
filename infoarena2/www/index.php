<?php

require_once("../config.php");
require_once("utilities.php");
require_once("wiki.php");

$x = array('Contest', 'Home', 'user/gigel?sort=1');
foreach ($x as $v) {
    echo url($v, array('sort' => '##$', 'correct' => 'da')) . "<br/>";
}

$page = request('page');
if (!preg_match('/^([a-z0-9_\-\/]*)$/i', $page)) {
    redirect(IA_URL);
}

$path = split('/', $page);
if (count($path) <= 0) {
    $path = array("");
}

$view = array();

switch (strtolower($path[0])) {
    case 'user':
        echo 'here comes user controller';
        break;

    case 'register':
        echo 'user registration page';
        break;

    case 'login':
        echo 'user login';
        break;

    case 'profile':
        echo 'edit your profile';
        break;

    case 'task':
        echo 'viewing task';
        break;

    default:
        // viewing generic wiki page
        if (0 >= strlen($page)) {
            $page = 'home';
        }

        $view['title'] = "Generic page: {$page}";
        $view['wikipage'] = $page;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title><?php getattr($view, 'title') ?></title>
</head>
<body>
    <h1><?php getattr($view, 'title') ?></h1>

    <div id="content">
        <?php

$wikipage = getattr($view, 'wikipage', null);
if (is_null($wikipage)) {
    echo '<div class="error">Controller does not dictate wiki page.</div>';
}
else {
    $buffer = wiki_process($view['wikipage'], $view);
    echo $buffer;
}

        ?>
    </div>
</body>
</html>

