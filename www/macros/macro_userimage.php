<?php

require_once(Config::ROOT.'www/format/format.php');
require_once(Config::ROOT.'common/common.php');

/**
 * Returns a an image which links to it's location of a giver username
 * and size
 *
 * @param  array $args      An array containing the user for whom to get the
 *                          avatar as well as the size
 * @return string
 */
function macro_userimage($args) {
    $user = $args['user'];

    $size_type = $args['size'];

    if (is_valid_size_type($size_type) == false) {
        return macro_error("Unkown size type \"".$size_type."\".");
    }

    return format_link(url_complex("avatar/full/".$user),
        format_user_avatar($user, $size_type, false), false);
}
