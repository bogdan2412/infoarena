<?php

class User extends Base {

  public static $_table = 'ia_user';

  function getScaledRating(): float {
    return rating_scale($this->rating_cache);
  }

  function getRatingGroup(): array {
    return rating_group($this->getScaledRating(), $this->isAdmin());
  }

  static function getIdFromUsername(string $username): int {
    $user = self::get_by_username($username);
    return $user->id ?? 0;
  }

  static function getByUsernamePlainPassword(string $username, string $password): ?User {
    $hash = user_hash_password($password, $username);
    return User::get_by_username_password($username, $hash);
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

  function isAdmin(): bool {
    return $this->security_level == 'admin';
  }

  function isEditable(): bool {
    return Identity::isAdmin() ||
      ($this->id == Identity::getId());
  }

  function hasSolvedTask($task): bool {
    $count = Model::factory('Job')
      ->where('user_id', $this->id)
      ->where('task_id', $task->id)
      ->where('score', 100)
      ->count();

    return ($count > 0);
  }

  function getArchiveTasks(bool $solved): array {
    $query = Model::factory('Task')
      ->table_alias('t')
      ->select('t.*')
      ->join('ia_score_user_round_task', [ 't.id', '=', 'surt.task_id' ], 'surt')
      ->join('ia_round', [ 'surt.round_id', '=', 'r.id' ], 'r')
      ->where('r.type', 'archive')
      ->where('surt.user_id', $this->id);

    if ($solved) {
      $query = $query->where('surt.score', 100);
    } else {
      $query = $query->where_lt('surt.score', 100);
    }

    return $query->find_many();
  }

}
