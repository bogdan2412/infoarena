<?php

// init SMF hooks to integrate with info-arena

$ia_integration = array(
    'integrate_verify_user' => 'ia_verify_user'
);

define("SMF_INTEGRATION_SETTINGS", serialize($ia_integration));




// Determine which SMF user is logged based on info-arena
// identity information
function ia_verify_user() {
    global $db_prefix;
    $key = '_ia_identity';

    session_start();

    // info-arena identity
    if (isset($_SESSION[$key])) {
        $ia_user = unserialize($_SESSION[$key]);
        // basic checks
        if (!is_array($ia_user) || !isset($ia_user['id'])
            || !isset($ia_user['username'])) {
            $ia_user = null;
        }
    }
    else {
        $ia_user = null;
    }

    if (!$ia_user) {
        // When info-arena session is no longer active,
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
		WHERE memberName = '" . addslashes($ia_user['username']) . "'
		LIMIT 1", __FILE__, __LINE__);
    $member_id = null;
	list($member_id) = mysql_fetch_row($result);
	mysql_free_result($result);

    if ($member_id) {
        return $member_id;
    }
    else {
        global $webmaster_email;
        fatal_error("Utilizatorul {$ia_user['username']} nu are echivalent in "
                    ."SMF! Va rugam sa contactati administratorul la adresa "
                    .$webmaster_email);
    }
}

?>
