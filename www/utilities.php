<?php

require_once(IA_ROOT_DIR . 'www/url.php');
require_once(IA_ROOT_DIR . 'common/db/tokens.php');

require_once(IA_ROOT_DIR . 'www/xhp/base/init.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/base.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/sitewide.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/sidebar.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/calendar.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/google_search.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/user_count.php');
require_once(IA_ROOT_DIR . 'www/xhp/ia/server_time.php');
require_once(IA_ROOT_DIR . 'www/xhp/ui/breadcrumbs.php');
require_once(IA_ROOT_DIR . 'www/xhp/ui/flash_message.php');

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
function redirect($absolute_url) {
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

/**
 * Composes a XHP object that provides the page frame, containing the default
 * header, footer, navigation bars, breadcrumbs and flash messages. The object
 * expects a <ia:content> child containg the main page content to be appended
 * before rendering.
 *
 * @param   string  $title
 * @param   string  $top_navbar_selected
 * @param   bool    $hide_sidebar_login_form
 * @param   string  $charset
 * @return  :ia:page
 */
function default_view_compose($title = null,
                              $top_navbar_selected = "infoarena",
                              $hide_sidebar_login_form = false,
                              $charset = "UTF-8") {
    // Retrieve recent page history to display as navigation breadcrumbs
    $recent_pages = getattr($_SESSION, '_ia_recent_pages', array());

    // Update recent page history
    $current_page = url_from_args($_GET);
    if (!request_is_post() &&
        !preg_match('/\/(json|plot|changes)\//', $current_page)) {
        $current_page_key = strtolower($current_page);
        $recent_pages[$current_page_key] =
            array($current_page, is_null($title) ? $current_page : $title);
        if (5 < count($recent_pages)) {
            array_shift($recent_pages);
        }
        $_SESSION['_ia_recent_pages'] = $recent_pages;
    } else {
        $current_page_key = null;
    }

    // Display flash message if it exists.
    if (isset($_SESSION['_ia_flash'])) {
        $flash_message =
          <ui:flash-message class={getattr($_SESSION, '_ia_flash_class')}>
            {$_SESSION['_ia_flash']}
          </ui:flash-message>;

        // Clear flash message.
        unset($_SESSION['_ia_flash']);
        if (isset($_SESSION['_ia_flash_class'])) {
            unset($_SESSION['_ia_flash_class']);
        }
    } else {
        $flash_message = <x:frag />;
    }

    // Add google custom search bar only if not in development mode
    if (!IA_DEVELOPMENT_MODE) {
        $google_search = <ia:google-search />;
    } else {
        $google_search = <x:frag />;
    }
    // Show the login form in the sidebar only if user is not authenticated.
    // Get the user's unread personal message count if he is.
    if (identity_is_anonymous()) {
        $sidebar_login =
          <ia:sidebar-login
            show_login_form={!$hide_sidebar_login_form}/>;
        $user = null;
        $user_pm_count = null;
    } else {
        $sidebar_login = <x:frag />;
        $user = identity_get_user();
        $user_pm_count = smf_get_pm_count(identity_get_username());
    }

    // Compose and return the page.
    return
      <ia:page title={$title} user={$user} charset={$charset}>
        <ia:header />
        <ia:top-navbar selected={$top_navbar_selected}
          user_pm_count={$user_pm_count} />

        <ia:left-col>
          <ia:left-navbar />
          {$google_search}
          <ia:calendar />
          {$sidebar_login}
          <ia:user-count />
          <ia:server-time />
          <ia:sidebar-ad />
        </ia:left-col>

        <ui:breadcrumbs entries={$recent_pages} current={$current_page_key} />
        {$flash_message}

        <ia:footer />
      </ia:page>;
}

/**
 * Renders an XHP based view and performs cleanup
 */
function render_and_die($xhp_page) {
    echo $xhp_page;
    if (IA_DEVELOPMENT_MODE) {
        // Development mode: display current page's log in site footer
        global $execution_stats;
        log_execution_stats();
        $buffer = $execution_stats['log_copy'];
        echo
          <textarea id="log" rows="50" cols="80">
            {$buffer}
          </textarea>;
    }
    session_write_close();
    save_tokens();
    die();
}

/*
 * DEPRECATED: Please use XHP based views.
 *
 * Execute as view. Variables in $view are placed in the local namespace as
 * variables. This is the preffered way of calling a template, because globals
 * are not easily accessible.
 *
 * @param   string  $view_file_name
 * @param   array   $view
 */
function compose_legacy_view($view_file_name, $view) {
    $_xhp_page = default_view_compose(
        getattr($view, 'title'),
        getattr($view, 'topnav_select', 'infoarena'),
        getattr($view, 'no_sidebar_login', false),
        getattr($view, 'charset', 'UTF-8'));

    require_once(IA_ROOT_DIR . 'www/views/utilities.php');
    // Expand $view members into global scope
    $GLOBALS['view'] = $view;
    foreach ($view as $view_hash_key => $view_hash_value) {
        if ($view_hash_key == 'view_hash_key') continue;
        if ($view_hash_key == 'view_hash_value') continue;
        if ($view_hash_key == 'view_file_name') continue;
        if ($view_hash_key == 'view') continue;
        $GLOBALS[$view_hash_key] = $view_hash_value;
        $$view_hash_key = $view_hash_value;
    }

    ob_start();
    include($view_file_name);
    $rendered = ob_get_clean();

    $_xhp_page->appendChild(
      <ia:content>
        {HTML($rendered)}
      </ia:content>);
    return $_xhp_page;
}

/*
 * DEPRECATED: Please use XHP based views.
 *
 * Executes view and then dies.
 */
function execute_view_die($view_file_name, $view) {
    render_and_die(compose_legacy_view($view_file_name, $view));
}

?>
