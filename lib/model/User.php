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
    $username = identity_get_username() ?? '';
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

  static function isAdmin(): bool {
    return self::hasSecurityLevel('admin');
  }

  static function isHelper(): bool {
    return self::hasSecurityLevel('helper');
  }

  static function isIntern(): bool {
    return self::hasSecurityLevel('intern');
  }

  private static function hasSecurityLevel(string $level): bool {
    global $identity_user;
    $realLevel = getattr($identity_user, 'security_level');
    return ($realLevel == $level);
  }

  static function isAnonymous(): bool {
    return identity_is_anonymous();
  }

  static function getCurrentId(): int {
    global $identity_user;
    return getattr($identity_user, 'id') ?? 0;
  }

  static function getCurrentUsername(): string {
    global $identity_user;
    return getattr($identity_user, 'username') ?? '';
  }

}
