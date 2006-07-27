<?php

require_once("../config.php");
require_once("config.php");
require_once("utilities.php");
require_once("wiki/wiki.php");
require_once("db.php");

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');
if (!preg_match('/^([a-z0-9_\-\/]*)$/i', $page)) {
    redirect(IA_URL);
}

// Do some monkey bussines based on the first part of $page.
$urlpath = split('/', $page);
if (count($urlpath) <= 0) {
    $urlpath = array("");
}

$view = array();

switch (strtolower($urlpath[0])) {
    case 'user':
        echo 'here comes user controller';
        break;
        
    case 'attachment':
        include('controllers/attachment.php');
        break;

    case 'register':
        include('controllers/register.php');
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

