<?php

require_once(IA_ROOT."common/db/user.php");
require_once(IA_ROOT."common/db/smf.php");
require_once(IA_ROOT."www/format/format.php");

// Create link to various SMF pages
function macro_smflink($args) {
    $link = getattr($args, 'link');
    $title = getattr($args, 'caption');

    if (!$link) {
        return macro_error('Expecting argument `link`');
    }
    if (!$title) {
        return macro_error('Expecting argument `caption`');
    }

    // some links require SMF member id
    if ('stats' == $link || 'pm' == $link) {
        $username = getattr($args, 'user');
        if (!$username) {
            return macro_error('Expecting argument `user`');
        }
        $user = user_get_by_username($username);
        if (!$user) {
            return macro_error("No such username");
        }
        $member_id = @smf_get_member_id($username);
        if (!$member_id) {
            return macro_error('User has no SMF account! Please '
                               .'contact an administrator!');
        }
    }
    switch ($link) {
        case 'stats':
            // link to user activity statistics
            $url = IA_SMF_URL.'?action=profile;u='.$member_id.';sa=statPanel';
            break;

        case 'pm':
            // send private message
            $url = IA_SMF_URL.'?action=pm;sa=send;u='.$member_id;
            // FIXME: Un-hack this. SMF should never display its login screen
            // See #202 for mor details.
            if (identity_anonymous()) {
                $url = url_login();
            }
            break;

        case 'board':
            // link to SMF board
            $board_id = getattr($args, 'board');
            if (!$board_id) {
                return macro_error('Expecting argument `board`');
            }
            $url = IA_SMF_URL."?board={$board_id}.0";
            break;

        case 'topic':
            // link to SMF topic 
            $topic_id = getattr($args, 'topic');
            if (!$topic_id) {
                return macro_error('Expecting argument `topic`');
            }
            $url = IA_SMF_URL."?topic={$topic_id}.0";
            break;

        case 'forum':
            // link to SMF
            $url = IA_SMF_URL;
            break;

        default:
            return macro_error('Invalid link type');
    }

    return format_link($url, $title);
}

?>
