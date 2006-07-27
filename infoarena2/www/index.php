<?php

require_once("utilities.php");
require_once("wiki.php");

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');
if (!preg_match('/^([a-z0-9_\-\/]*)$/i', $page)) {
    redirect(IA_URL);
}

// Do some monkey bussines based on the first part of $page.
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

    case 'wikitest':
        include('views/wikitest.php');
        break;

    default:
        // viewing generic wiki page
        if (0 >= strlen($page)) {
            $page = 'home';
        }

        $view['title'] = "Generic page: {$page}";
        $view['wikipage'] = $page;
        include('views/wikipage.php');
}

?>

