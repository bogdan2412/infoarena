<?php

require_once(IA_ROOT_DIR . 'www/url.php');
require_once(IA_ROOT_DIR . 'common/db/tokens.php');
// Wrapper around htmlentities which defaults charset to UTF-8
function html_escape($string, $quote_style = ENT_COMPAT, $charset = "UTF-8")
{
    return htmlentities($string, $quote_style, $charset);
}

function xml_escape($string, $quote_style = ENT_COMPAT, $charset = "UTF-8")
{
    $xml = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;',
        '&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;',
        '&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;',
        '&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;',
        '&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;',
        '&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;',
        '&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;',
        '&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;',
        '&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;',
        '&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;',
        '&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;',
        '&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;',
        '&#250;','&#251;','&#252;','&#253;','&#254;','&#255;', '&#8221;', 
        '&#8222;',
    );
    $html = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;',
        '&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;',
        '&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;',
        '&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;',
        '&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;',
        '&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;',
        '&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;',
        '&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;',
        '&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;',
        '&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;',
        '&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;',
        '&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;',
        '&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;',
        '&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;',
        '&thorn;','&yuml;', '&rdquo;', '&bdquo;',
    );
    $string = html_escape($string, $quote_style, $charset);
    $string = str_replace($html, $xml, $string);
    $string = str_ireplace($html, $xml, $string);
    return $string;
}

// returns an array of all arguments in REQUEST
function request_args() {
    $result = array();
    foreach($_REQUEST as $key => $value) {
        $result[] = $key;
    }
    return $result;
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
function redirect($absolute_url, $code = 302) {
    log_assert($code === 301 || $code === 302);
    if ($code === 301) {
        header('HTTP/1.1 301 Moved Permanently');
    } elseif ($code === 302) {
        header('HTTP/1.1 302 Found');
    }
    header("Location: {$absolute_url}\n\n");
    session_write_close();
    save_tokens();
    die();
}

// Checks if the referer is the same as the host
function http_referer_check() {
    return true;
    //FIXME: this is broken
    $HTTP_REFERER = getattr($_SERVER, 'HTTP_REFERER');
    $HTTP_HOST = getattr($_SERVER, 'HTTP_HOST');
    return $HTTP_REFERER==null || substr($HTTP_REFERER, 0, (strlen($HTTP_HOST)+7)) == "http://".$HTTP_HOST;
}

// Client side caching... let's save some bandwidth
// If you call this and the client has a version which is newer that $last_modified
// then the request aborts.
// Otherwise the client is told to only ask again after $cache_age seconds.
//
// This function analyzes http headers and looks for an If-Modified-Since header.
function http_cache_check($last_modified, $cache_age = IA_CLIENT_CACHE_AGE) {
    if (!IA_CLIENT_CACHE_ENABLE) {
        return;
    }

    $headers = apache_request_headers();
    if (isset($headers['If-Modified-Since'])) {
        // we split it due to some bug in Mozilla < v6
        $modified_since = explode(';', $headers['If-Modified-Since']);
        $modified_since = strtotime($modified_since[0]);
    } else {
        $modified_since = 0;
    }

    // Serve HTTP headers to cache file
    header("Cache-Control: max-age: ".IA_CLIENT_CACHE_AGE
           ." , public, must-revalidate");
    // Additional headers, obsolete in HTTP 1.1. browsers
    header('Expires: '.gmdate('D, d M Y H:i:s',
              time()+IA_CLIENT_CACHE_AGE).' GMT');

    if ($last_modified !== false && $modified_since >= $last_modified) {
        // Client's cache is up to date, yey!
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified)
               .' GMT', true, 304);
        //log_print('CACHE: Client hit');
        die();
    } else {
        //log_print('CACHE: Client miss');
        // Client's cache is missing / out-dated
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified)
               .' GMT', true, 200);
    }
}

// Serve static file through HTTP
// NOTE: cache check enabled by default
// WARNING: this function does not return
function http_serve($disk_file_name, $http_file_name, $mime_type = null, $cache_check = true) {
    if (is_null($mime_type)) {
        $mime_type = "application/octet-stream";
    }

    global $IA_SAFE_MIME_TYPES;
    if (!in_array($mime_type, $IA_SAFE_MIME_TYPES)) {
        $disposition = "attachment";

        // WARNING: *don't* add cache or the second time an attachment is downloaded in IE it will load inline
    } else {
        $disposition = "inline";

        // Cache magic.
        if ($cache_check) {
            http_cache_check(filemtime($disk_file_name));
        }
    }

    // HTTP headers.  
    header("Content-Type: {$mime_type}");
    header("Content-Disposition: {$disposition}; filename="
           .urlencode($http_file_name).";");
    $fsize = filesize($disk_file_name);
    header("Content-Length: " . $fsize);

    $fp = fopen($disk_file_name, "rb");
    log_assert($fp);

    // Serve file
    $written = fpassthru($fp);
    if ($written != $fsize) {
        log_error("fpassthru failed somehow.");
    }
    fclose($fp);
    die();
}

// Die with a http error.
function die_http_error($code = 404, $msg = "File not found") {
    header("HTTP/1.1 $code");
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
}

// Execute view and then die.
function execute_view_die($view_file_name, $view) {
    execute_view($view_file_name, $view);
    if (IA_DEVELOPMENT_MODE) {
        log_execution_stats();
    }
    session_write_close();
    save_tokens();
    die();
}

?>
