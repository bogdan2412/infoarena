<?php

require_once("../config.php");
require_once("config.php");
require_once("utilities.php");
require_once("wiki/wiki.php");
require_once("db.php");

session_start();

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');
if (!preg_match('/^([a-z0-9_\-\/]*)$/i', $page)) {
    redirect(IA_URL);
}

// Redirectam la home.
if ($page == "") {
    $page = "home";
}

// Do some monkey bussines based on the first part of $page.
$urlpath = split('/', $page);
if (count($urlpath) <= 0) {
    $urlpath = array("");
}

switch (strtolower($urlpath[0])) {
    case 'user':
        echo 'here comes user controller';
        break;
        
    case 'register':
        include('controllers/register.php');
        break;

    case 'login':
        echo 'user login';
        break;

    case 'profile':
        include('controllers/profile.php');
        break;

    case 'task':
        echo 'viewing task';
        break;

    case 'json':
        // this controller serves as a data server for AJAX calls
        include('controllers/json.php');
        break;


    default:
        // viewing generic wiki page
        if (0 >= strlen($page)) {
            $page = 'home';
        }

        include('controllers/wiki.php');
		break;
}

?>

