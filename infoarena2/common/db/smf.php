<?php

// Various routines used for tight SMF integration.
//
// There is no decent way of linking native SMF APIs so we resort
// to duplication and hard-coding.
// These must be updated every time we switch SMF distributions.

// Creates SMF user from a regular info-arena user.
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

    return db_insert(DB_SMF_PREFIX.'members', $fields);
}

// Updates SMF user information from a regular info-arena user.
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

        // ID_GROUP 1 is forum administrator
        'ID_GROUP' => ('admin' == $ia_user['security_level'] ? 1 : null),
    );

    $where = sprintf("memberName='%s'", db_escape($ia_user['username']));
    $res = db_update(DB_SMF_PREFIX.'members', $fields, $where);

    log_assert(1 >= $res, "smf_update_user() affected multiple rows in table "
                          ."ia_user! Needs serious attention!");
    return $res;
}

// Returns SMF member id from infoarena username
function smf_get_member_id($username) {
    $prefix = DB_SMF_PREFIX;
    $query = "
        SELECT ID_MEMBER FROM {$prefix}members
        WHERE memberName = '%s'
    ";
    return db_query_value(sprintf($query, $username));
}

?>
