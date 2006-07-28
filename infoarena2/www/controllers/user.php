<?php

// Initialize view parameters.
$view = array();

// see what user we are viewing
$vuser = getattr($urlpath, 1, null);
$user_info = user_get_by_username($vuser);

if (!$user_info) {
    // Bad username, redirect to home
    flash_error("Ati incercat sa vedeti profilul unui utilizator inexistent");
    redirect(url(""));
}

// page title
$view['title'] = $vuser . "'s profile";

// check permisions
$detail_view = identity_can('user-details');
$view['detail_view'] = $detail_view;
$view['user_info'] = $user_info;

// attach form is displayed for the first time or a validation error occured
execute_view('views/user.php', $view);
?>