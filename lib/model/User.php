<?php

class User extends Base {

  public static $_table = 'ia_user';
  private static $avatarAttachment = null; // not yet loaded
  private static $avatarAttachmentLoaded = false;

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

  function getAccountUrl(): string {
    return url_account();
  }

  private function getAvatarAttachment(): ?Attachment {
    if (!$this->avatarAttachmentLoaded) {
      $this->avatarAttachmentLoaded = true;
      $this->avatarAttachment = Attachment::get_by_page_name(
        'utilizator/' . $this->username, 'avatar');
    }
    return $this->avatarAttachment;
  }

  function hasAvatar(): bool {
    $att = $this->getAvatarAttachment();
    return $att && file_exists($att->getFileName());
  }

  function getNoAvatarUrl(): string {
    return Config::URL_PREFIX . 'static/images/user.svg';
  }

  function getFullAvatarUrl(): string {
    return $this->hasAvatar()
      ? sprintf('%sdownload/utilizator/%s/avatar', Config::URL_PREFIX, $this->username)
      : $this->getNoAvatarUrl();
  }

  function getAvatarUrl(string $size): string {
    $geom = Config::GEOMETRY[$size] ?? '';
    if ($this->hasAvatar() && $geom) {
      $url = sprintf('%sresize/utilizator/%s/avatar/%s',
                     Config::URL_PREFIX, $this->username, $size);
      return $url;
    } else {
      return $this->getNoAvatarUrl();
    }
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

  function getProfileUrl(): string {
    return url_user_profile($this->username);
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
