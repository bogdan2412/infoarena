<?php

require_once(IA_ROOT_DIR."www/url.php");

// Escapes a string from html. Better than html entities because it's
// shorter and it handles utf-8.
function xmlesc($arg) {
    return htmlentities($arg, ENT_COMPAT, 'UTF-8');
}

function request($param, $default = null) {
    return getattr($_REQUEST, $param, $default);
}

// Returns boolean whether current request method is POST
function request_is_post() {
    return ('post' == strtolower(getattr($_SERVER, 'REQUEST_METHOD')));
}

// Call this function for a http-level redirect.
// NOTE: this function DOES NOT RETURN.
//
// NOTE: this must be called before any other output.
// If output started before issuing a redirect means you're either
// printing stuff too early or you're trying to redirect too late (view?).
// Either way, it is a bug and it must be solved rather than handled gracefully
//
// FIXME: bool to se ia_redirect to REQUEST_URI? might be usefull.
function redirect($absolute_url) {
    header("Location: {$absolute_url}\n\n");
    session_write_close();
    die();
}

// Checks if the referer is the same as the host
function http_referer_check() {
    $HTTP_REFERER = getattr($_SERVER, 'HTTP_REFERER');
    $HTTP_HOST = getattr($_SERVER, 'HTTP_HOST');
    return $HTTP_REFERER==null || substr($HTTP_REFERER, 0, (strlen($HTTP_HOST)+7)) == "http://".$HTTP_HOST;
}

// Die with a http error.
function die_http_error($code = 404, $msg = "File not found") {
    header("HTTP/1.0 $code");
    echo '<h1>'.$msg.'</h1>';
    echo '<p><a href="'.IA_URL.'">Inapoi la prima pagina</a></p>';
    die();
}

// Use flash() to display a message right after redirecting the user.
// Message is displayed only once.
function flash($message, $style_class = null) {
    global $_SESSION;
    $_SESSION['_ia_flash'] = $message;
    $_SESSION['_ia_flash_class'] = $style_class;
}

// This is a simple binding for flash() with a fixed CSS style class
// for displaying error messages
function flash_error($message) {
    flash($message, 'flashError');
}

// Execute a view. Variables in $view are placed in the
// local namespace as variables. This is the preffered
// way of calling a template, because globals are not
// easily accessible.
function execute_view($view_file_name, $view) {
    global $identity_user;

    // retrieve recent page history
    // some pages display it as navigation breadcrumbs
    $recent_pages = getattr($_SESSION, '_ia_recent_pages', array());

    // update recent page history
    $query = url_from_args($_GET);
    if (!preg_match('/\/(json|plot|changes)\//', $query) && !request_is_post()) {
        $hashkey = strtolower($query);
        $recent_pages[$hashkey] = array($query, getattr($view, 'title', $query)); 
        if (5 < count($recent_pages)) {
            array_shift($recent_pages);
        }
        $_SESSION['_ia_recent_pages'] = $recent_pages;
    }

    // let view access recent_pages
    $view['current_url_key'] = strtolower($query);
    $view['recent_pages'] = $recent_pages;

    // give access to request statistics
    if (IA_DEVELOPMENT_MODE) {
        global $execution_stats;
        $view['execution_stats'] = $execution_stats;
    }

    // expand $view members into global scope
    $GLOBALS['view'] = $view;

    foreach ($view as $view_hash_key => $view_hash_value) {
        if ($view_hash_key == 'view_hash_key') continue;
        if ($view_hash_key == 'view_hash_value') continue;
        if ($view_hash_key == 'view_file_name') continue;
        if ($view_hash_key == 'view') continue;
        //echo "added $view_hash_key = $view_hash_value into globals";
        $GLOBALS[$view_hash_key] = $view_hash_value;
        $$view_hash_key = $view_hash_value;
    }

    // NOTE: no includes here, unless you want to get
    // warnings about function redeclaration.
    include($view_file_name);
    //include('views/vardump.php');
}

// Execute view and then die.
function execute_view_die($view_file_name, $view) {
    execute_view($view_file_name, $view);
    if (IA_DEVELOPMENT_MODE) {
        log_execution_stats();
    }
    session_write_close();
    die();
}

?>
