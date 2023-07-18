<?php

class User extends Base {

  public static $_table = 'ia_user';

  static function getIdFromUsername(string $username): int {
    $user = self::get_by_username($username);
    return $user->id ?? 0;
  }

  static function getAccountUrl(): string {
    return url_account();
  }

  static function getAvatarUrl(string $username, string $size): string {
    return url_user_avatar($username, $size);
  }

  static function getCurrentUserMonitorUrl(): string {
    $username = Identity::getUsername();
    return self::getMonitorUrl($username);
  }

  static function getMonitorUrl(string $username): string {
    return $username
      ? url_monitor([ 'user' => $username])
      : url_monitor();
  }

  static function getProfileUrl(string $username): string {
    return url_user_profile($username);
  }

  static function getRatingUrl(string $username): string {
    return url_user_rating($username);
  }

  function isEditable(): bool {
    return Identity::isAdmin() ||
      ($this->id == Identity::getId());
  }

}
