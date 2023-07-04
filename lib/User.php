<?php

class User {

  static function getAccountUrl(): string {
    return url_account();
  }

  static function getAvatarUrl(string $username, string $size): string {
    return url_user_avatar($username, $size);
  }

  static function getCurrentUserMonitorUrl(): string {
    $username = identity_get_username() ?? '';
    return self::getMonitorUrl($username);
  }

  static function getMonitorUrl(string $username): string {
    return url_monitor([ 'user' => $username]);
  }

  static function getProfileUrl(string $username): string {
    return url_user_profile($username);
  }

  static function getRatingUrl(string $username): string {
    return url_user_rating($username);
  }

  static function isAdmin(): bool {
    global $identity_user;
    $secLevel = getattr($identity_user, 'security_level');
    return ($secLevel == 'admin');
  }

  static function isAnonymous(): bool {
    return identity_is_anonymous();
  }

  static function getCurrentUsername(): string {
    global $identity_user;
    return getattr($identity_user, 'username') ?? '';
  }

}
