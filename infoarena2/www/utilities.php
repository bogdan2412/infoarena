<?php

require_once(IA_ROOT."www/url.php");

function request($param, $default = null) {
    return getattr($_REQUEST, $param, $default);
}

// Returns boolean whether current request method is POST
function request_is_post() {
    $post = ('post' == strtolower(getattr($_SERVER, 'REQUEST_METHOD')));
    return $post;
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
    log_print("HTTP Redirect to $absolute_url from {$_SERVER['QUERY_STRING']}");
    header("Location: {$absolute_url}\n\n");
    session_write_close();
    die();
}

// Die with a http error.
function die_http_error($code = 404, $msg = "File not found") {
    log_print("HTTP ERROR $code $msg");
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
    session_write_close();
    die();
}

// Resize 2D coordinates according to 'textual' instructions
// Given a (width, height) pair, resize it (compute new pair) according to
// resize instructions.
//
// Resize instructions are in WxH format, where W and/or H can be a
// percentage (with %). By default it keeps the original aspect ratio,
// prefix with @ to avoid this.
//
// Alternatively you can just use X% to resize both dimensions.
//
// Returns 2-element array: (width, height) or null if invalid format
// FIXME: Does not belong here.
function resize_coordinates($width, $height, $resize) {
    // log_print("Parsing resize '$resize'");
    // Both with and height.
    if (preg_match('/^(\@?)([0-9]+\%?)x([0-9]+\%?)$/', $resize, $matches)) {
        $flags = $matches[1];
        $targetw = (float)$matches[2];
        $targeth = (float)$matches[3];

        if (preg_match("/\%/", $targetw)) {
            $targetw = $width * preg_match("/[0-9]+/", $targetw) / 100.0;
        }
        if (preg_match("/\%/", $targeth)) {
            $targeth = $height * preg_match("/[0-9]+/", $targeth) / 100.0;
        }

        // log_print("$targetw x $targeth with flags $flags");

        if ($flags != '@') {
            $targetw = min($targeth * $width / $height, $width);
            $targeth = min($targetw * $height / $width, $height);
            $targetw = min($targeth * $width / $height, $width);
        }
    } else if (preg_match('/^([0-9]+)\%$/', $resize, $matches)) {
        //log_print("Scaling at ".$matches[1]."%.");
        $targetw = $width * $matches[1] / 100.0;
        $targeth = $height * $matches[1] / 100.0;
    } else {
        return null;
    }

    return array(floor($targetw), floor($targeth));
}

?>
