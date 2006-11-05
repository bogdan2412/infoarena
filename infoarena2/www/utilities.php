<?php

function request($paramName, $defaultValue = null) {
    return getattr($_REQUEST, $paramName, $defaultValue);
}

// Call this function for a http-level redirect.
// NOTE: this function DOES NOT RETURN.
// FIXME: Detect if output started and still do a redirect?
// FIXMENOT: If output started before issuing a redirect means you're either
// printing stuff too early or you're trying to redirect too late (view?).
// Either way, it is a bug and it must be solved rather than handled gracefully
// FIXME: Is that even remotely possible?
// FIXME: Would be usefull for debugging though.
function redirect($absoluteUrl) {
    log_print("HTTP Redirect to $absoluteUrl from {$_SERVER['QUERY_STRING']}");
    header("Location: {$absoluteUrl}\n\n");
    session_write_close();
    die();
}

// Die with a http error.
function die_http_error($code = 404, $msg = "File not found") {
    log_print("HTTP ERROR $code $msg");
    header("HTTP/1.0 $code $http_msg");
    echo '<h1>'.$msg.'</h1>';
    echo '<p><a href="'.IA_URL.'">Inapoi la prima pagina</a></p>';
    die();
}

// Build a simple href
function href($url, $content) {
    return "<a href=\"$url\">$content</a>";
}

// Link to an username.
// FIXME: colored by rating and stuff.
function format_user_link($username) {
    return href(url("user/$username"), $username);
}

// Get an url.
// The params array contains http get parameter,
// it's formatted in the end result as a series
// of key1=value1&key2=value2.
//
// NOTE: Only use this function for urls.
// NOTE: don't add ?x=y stuff in document.
//
// If $absolute is true(default false) then the server will be
// included in the url.
function url($document, $args = array(), $absolute = false) {
    log_assert(false === strpos($document, '?'), 'Page name contains ?');
    log_assert_is_array($args, "Argument list must be an array");
    log_assert(!array_key_exists("page", $args), "Argument list contains page");

    $args['page'] = $document;
    return url_from_args($args, $absolute);
}

// Construct an URL from an argument list.
// These are the exact $args you will receive in $_GET
function url_from_args($args, $absolute = false)
{
    // First part.
    if ($absolute) {
        $url = IA_URL;
    } else {
        $url = IA_URL_PREFIX;
    }
    $url .= log_assert_getattr($args, "page");
    
    // Actual args.
    $first = true;
    foreach ($args as $k => $v) {
        if ($k != 'page') {
            $url .= ($first ? "?" : "&amp;");
            $first = false;
            $url .= $k . '=' . urlencode($v);
        }
    }

    return $url;
}

// Get an url for an attachement
function attachment_url($page, $file)
{
    return url($page, array('action' => 'download', 'file' => $file));
}


// Use flash() to display a message right after redirecting the user.
// Message is displayed only once.
function flash($message, $styleClass = null) {
    global $_SESSION;
    $_SESSION['_flash'] = $message;
    $_SESSION['_flash_class'] = $styleClass;
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
    $recent_pages = getattr($_SESSION, 'recent_pages', array());

    // update recent page history
    $query = url_from_args($_GET);
    $hashkey = strtolower($query);
    $recent_pages[$hashkey] = array($query, getattr($view, 'title', $page)); 
    if (5 < count($recent_pages)) {
        array_shift($recent_pages);
    }
    $_SESSION['recent_pages'] = $recent_pages;

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

    require_once('views/utilities.php');
    include($view_file_name);
    //include('views/vardump.php');
}

// Execute and the die.
function execute_view_die($view_file_name, $view) {
    execute_view($view_file_name, $view);
    session_write_close();
    die();
}

// smart ass diff
function string_diff($string1, $string2) {
    $name1 = tempnam(IA_ATTACH_DIR, "ia");
    $name2 = tempnam(IA_ATTACH_DIR, "ia");
    $fp1 = fopen($name1, "w");
    if (!$fp1) {
        flash_error("Eroare la comparare!");
        request(url(''));
    }
    $string1 .= "\n";
    fputs($fp1, $string1);
    fclose($fp1);

    $fp2 = fopen($name2, "w");
    if (!$fp2) {
        flash_error("Eroare la comparare!");
        request(url(''));
    }
    $string2 .= "\n";
    fputs($fp2, $string2);
    fclose($fp2);

    ob_start();
    system("diff -au ".$name1." ".$name2);
    $ret = ob_get_contents();
    ob_end_clean();
    if (!unlink($name1)) {
        flash_error("Eroare la comparare!");
        request(url(''));
    }
    if (!unlink($name2)) {
        flash_error("Eroare la comparare!");
        request(url(''));
    }
    return $ret;
}

// send mail function, does it need a description?
function send_email($to, $subject, $message,
                    $from = IA_MAIL_SENDER_NO_REPLY, $reply = 0)
{
    /** TODO FIXME: when server can send emails, fix this function
                    by removing all echo calls **/
    echo "<strong>Currently the server can't send emails, " . 
         "so the contents of the email is printed on screen instead. " .
         "Please ignore errors and when fixed remove this! " .
         "(utilities.php -> function send_mail)</strong><br><br>";

    // if we don't specify reply-to, should be the same as the from
    if ($reply === 0) {
        $reply = $from;
    }

    // put [info-arena] tag in mail subject
    $subject = '[info-arena] ' . $subject;

    // word-wrap message, some mail-clients are stupid
    $message = wordwrap($message, 70);
    
    $headers = 'From: ' . $from . "\r\n" .
               'Reply-To: ' . $reply . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
    echo $to . '<br>' . $subject . '<br>' . $message; // debug info
}

// Resize 2D coordinates according to 'textual' instructions
// Given a (width, height) pair, resize it (compute new pair) according to
// resize instructions.
//
// Resize instructions format may be one of the following:
// # example    # description
// 100x100      keep aspect ratio, resize as to fit a 100x100 box
//              Coordinates are not enlarged if they already fit the given box.
// @100x100     keep aspect ratio, resize as to exactly fit a 100x100 box
//              Enlarge coordinates if necessary
// !50x86       ignore aspect ratio, resize to exactly 50x86
// 50%          scale dimensions. only integer percentages allowed
//
// Returns 2-element array: (width, height) or null if invalid format
function resize_coordinates($width, $height, $resize) {
    // remove @
    if (0 < strlen($resize) && $resize[0] == '@') {
        $enlarge = true;
        $resize = substr($resize, 1);
    }
    else {
        $enlarge = false;
    }

    $ratio = 1.0;

    // 100x100 or @100x100
    if (preg_match('/^([0-9]+)x([0-9]+)$/', $resize, $matches)) {
        $boxw = (float)$matches[1];
        $boxh = (float)$matches[2];

        if ($width > $boxw || $enlarge) {
            $ratio =  $boxw / $width;
        }
        if ($height * $ratio > $boxh) {
            $ratio *= $boxh / ($height * $ratio);
        }
    }
    // 50%
    elseif (preg_match('/^([0-9]+)%$/', $resize, $matches)) {
        $ratio = (float)$matches[1] / 100;
    }
    // !50x86
    elseif (preg_match('/^!([0-9]+)x([0-9]+)$/', $resize, $matches)) {
        $width = (float)$matches[1];
        $height = (float)$matches[2];
    }
    else {
        return null;
    }

    return array(floor($ratio * $width), floor($ratio * $height));
}

?>
