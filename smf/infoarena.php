<?php

// link some infoarena API
//
// WARNING:
// This is really dirty practice! We can't do anything but hope there are
// no serious name clashes between infoarena and SMF.
// Somebody, please code some namespaces in PHP!
//
// NONE: We cannot link infoarena's db.php or log.php since it clashes
// with SMF built-ins. We'll have to program our way around them.

define("IA_FROM_SMF", true);

require_once(IA_ROOT_DIR."common/common.php");
check_requirements();
require_once(IA_ROOT_DIR."common/security.php");
require_once(IA_ROOT_DIR."www/utilities.php");
require_once(IA_ROOT_DIR."www/identity.php");


// init SMF hooks to integrate with infoarena
// These are native integration features built into SMF. Sweet!
$ia_integration = array(
    'integrate_verify_user'  => 'ia_verify_user',
    'securityDisable'        => true,
    'databaseSession_enable' => false,
    'theme_default'          => 1,
);

define("SMF_INTEGRATION_SETTINGS", serialize($ia_integration));


// restore infoarena session (if such a session exists)
identity_restore();


// Determine which SMF user is logged based on infoarena
// identity information.
// This is an integration hook.
function ia_verify_user() {
    global $identity_user;
    global $db_prefix;

    if (!$identity_user) {
        // When infoarena session is no longer active,
        // destroy any SMF session still hanging active
        global $cookiename;
        unset($_COOKIE[$cookiename]);
        unset($_SESSION['login_'.$cookiename]);

        return false;
    }

    // relate ia_user with SMF member
    $result = db_query("
                SELECT ID_MEMBER 
                FROM {$db_prefix}members
                WHERE memberName = '" . addslashes($identity_user['username']) . "'
                LIMIT 1", __FILE__, __LINE__);
    $member_id = null;
    list($member_id) = mysql_fetch_row($result);
    mysql_free_result($result);

    if ($member_id) {
        return $member_id;
    }
    else {
        global $webmaster_email;
        fatal_error("Utilizatorul {$identity_user['username']} nu are "
                    ."echivalent in SMF! Va rugam sa contactati "
                    ."administratorul la adresa "
                    .$webmaster_email);
    }
}

?>
