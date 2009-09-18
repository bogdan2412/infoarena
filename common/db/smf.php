<?php

require_once(IA_ROOT_DIR."common/db/db.php");

// Various routines used for tight SMF integration.
//
// There is no decent way of linking native SMF APIs so we resort
// to duplication and hard-coding.
// These must be updated every time we switch SMF distributions.

// Creates SMF user from a regular infoarena user.
// Returns newly created user id.
function smf_create_user($ia_user) {
    $fields = array(
        'memberName' => $ia_user['username'],
        'dateRegistered' => time(),
        //'posts' => 0,
        //'lngfile' => '',
        'realName' => $ia_user['full_name'],
        //'pm_ignore_list' => '',

        // NOTE: We copy the password verbatim since ia2 uses same password
        // hasing scheme as SMF
        'passwd' => $ia_user['password'],

        'emailAddress' => $ia_user['email'],
        //'personalText' => '',
        //'websiteTitle' => '',
        //'websiteUrl' => '',
        'location' => getattr($ia_user, 'city'),
        //'ICQ' => '',
        //'AIM' => '',
        //'YIM' => '',
        //'MSN' => '',
        //'timeFormat' => '',
        //'signature' => '',
        //'avatar' => '',
        'pm_email_notify' => 1,
        //'usertitle' => '',
        //'memberIP' => '',
        //'secretQuestion' => '',
        //'secretAnswer' => '',
        'is_activated' => "1",
        //'validation_code' => '',
        //'additionalGroups' => '',
        //'smileySet' => '',
        //'passwordSalt' => '\'' . substr(md5(rand()), 0, 4) . '\'',
        //'messageLabels' => '',
        //'buddy_list' => '',
        //'memberIP2' => '',

        // ID_GROUP 1 is forum administrator
        'ID_GROUP' => ('admin' == $ia_user['security_level'] ? 1 : null),
    );

    return db_insert(IA_SMF_DB_PREFIX.'members', $fields);
}

// Updates SMF user information from a regular infoarena user.
function smf_update_user($ia_user) {

    $fields = array(
        'memberName' => $ia_user['username'],
        // 'dateRegistered' => time(),
        //'posts' => 0,
        //'lngfile' => '',
        'realName' => $ia_user['full_name'],
        //'pm_ignore_list' => '',

        // NOTE: We copy the password verbatim since ia2 uses same password
        // hasing scheme as SMF
        'passwd' => $ia_user['password'],

        'emailAddress' => $ia_user['email'],
        //'personalText' => '',
        //'websiteTitle' => '',
        //'websiteUrl' => '',
        'location' => getattr($ia_user, 'city'),
        //'ICQ' => '',
        //'AIM' => '',
        //'YIM' => '',
        //'MSN' => '',
        //'timeFormat' => '',
        //'signature' => '',
        //'avatar' => '',
        //'pm_email_notify' => 1,
        //'usertitle' => '',
        //'memberIP' => '',
        //'secretQuestion' => '',
        //'secretAnswer' => '',
        //'is_activated' => "1",
        //'validation_code' => '',
        //'additionalGroups' => '',
        //'smileySet' => '',
        //'passwordSalt' => '\'' . substr(md5(rand()), 0, 4) . '\'',
        //'messageLabels' => '',
        //'buddy_list' => '',
        //'memberIP2' => '',
    );

    $smf_user = smf_get_member_by_name($ia_user['username']);

    $additional_groups = array();
    if (strlen($smf_user['additional_groups']) > 0) {
        $additional_groups = explode(',', $smf_user['additional_groups']);
    }

    // Check if user is a smf admin
    // SMF holds the ID of the first group in id_group
    // and the others in additional_groups separated by , (comma)
    $is_smf_admin = false;
    if ($smf_user['id_group'] == 1) {
        $is_smf_admin = true;
    }

    if (in_array(1, $additional_groups)) {
        $is_smf_admin = true;
    }

    // If user is admin on IA but not on SMF he is promoted
    if ($ia_user['security_level'] == 'admin' && !$is_smf_admin) {
        $fields['ID_GROUP'] = 1;

        if ($smf_user['id_group'] != 0) {
            $additional_groups[] = $smf_user['id_group'];
        }
        $fields['additionalGroups'] = implode(',', $additional_groups);
    }

    // If user is admin on smf but not on IA he is demoted
    if ($ia_user['security_level'] != 'admin' && $is_smf_admin) {
        if ($smf_user['id_group'] == '1') {
            $fields['ID_GROUP'] = 0;
        } else {
            $groups = '';
            foreach ($additional_groups as $group) {
                // Strip group 1 from additionalGroups
                if ($group == 1) {
                    continue;
                }

                if (strlen($groups) > 0) {
                    $groups .= ',';
                }
                $groups .= $group;
            }

            $fields['additionalGroups'] = $groups;
        }
    }

    $where = sprintf("memberName='%s'", db_escape($ia_user['username']));
    $res = db_update(IA_SMF_DB_PREFIX.'members', $fields, $where);

    log_assert(1 >= $res, "smf_update_user() affected multiple rows in table "
                          ."ia_user! Needs serious attention!");
    return $res;
}

// Returns SMF member id from infoarena username
function smf_get_member_id($username) {
    $prefix = IA_SMF_DB_PREFIX;
    $query = "
        SELECT ID_MEMBER FROM {$prefix}members
        WHERE memberName = '%s'
    ";
    return db_query_value(sprintf($query, db_escape($username)));
}

function smf_get_member_by_name($username) {
    $prefix = IA_SMF_DB_PREFIX;
    $query = "
        SELECT ID_MEMBER AS id, memberName AS user_name,
        ID_GROUP AS id_group, additionalGroups AS additional_groups
        FROM {$prefix}members
        WHERE memberName = '".db_escape($username)."'
    ";

    return db_fetch($query);
}

?>
