<?php

class Identity {

  static ?User $user = null;

  /**
   * @return bool True iff a user with the given ID exists.
   */
  static function set(int $userId): bool {
    $u = User::get_by_id($userId);
    if ($u) {
      self::$user = $u;
      return true;
    } else {
      return false;
    }
  }

  static function get(): ?User {
    return self::$user;
  }

  static function getId(): int {
    return self::$user->id ?? 0;
  }

  static function getUsername(): string {
    return self::$user->username ?? '';
  }

  static function isAnonymous(): bool {
    return self::$user == null;
  }

  static function isLoggedIn(): bool {
    return self::$user != null;
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
    $realLevel = self::$user->security_level ?? '';
    return ($realLevel == $level);
  }

  static function isBanned(): bool {
    return self::$user->banned ?? false;
  }

  static function requireLogin(): void {
    if (self::isAnonymous()) {
      Util::redirectToLogin();
    }
  }

  static function enforce(bool $hasPrivilege): void {
    if (self::isBanned()) {
      FlashMessage::addError('Contul tău este blocat. Dacă nu știm noi de ce, știi tu.');
      Util::redirectToHome();
    } else if ($hasPrivilege) {
      return;
    } else if (!self::isLoggedIn()) {
      Util::redirectToLogin();
    } else {
      FlashMessage::addError('Nu ai permisiunea să faci această operație.');
      Util::redirectToHome();
    }
  }

  static function ownsJob(Job $job): bool {
    $isCreator = (self::getId() == $job->user_id);
    return
      self::isAdmin() ||
      self::isIntern() ||
      $isCreator;
  }

  static function ownsRound(array $round): bool {
    $isCreator = (self::getId() == $round['user_id']);
    $isUserDefined = ($round['type'] == 'user-defined');
    return
      self::isAdmin() ||
      self::isIntern() ||
      ($isCreator && $isUserDefined);
  }

  static function ownsTask(Task $task): bool {
    $isCreator = (self::getId() == $task->user_id);
    return
      self::isAdmin() ||
      self::isIntern() ||
      (self::isHelper() && $isCreator);
  }

  // TODO Merge methods that take array parameters with methods that take
  // object parameters, e.g. mayViewTextblock() with Textblock::isViewable().

  static function mayCreateRound(string $roundType): bool {
    return
      self::isAdmin() ||
      self::isIntern() ||
      (self::isLoggedIn() && ($roundType == 'user-defined'));
  }

  static function enforceCreateRound(string $roundType): void {
    self::enforce(self::mayCreateRound($roundType));
  }

  static function mayCreateTask(): bool {
    return
      self::isAdmin() ||
      self::isIntern() ||
      self::isHelper();
  }

  static function enforceCreateTask(): void {
    self::enforce(self::mayCreateTask());
  }

  static function mayDeleteRevision(): bool {
    return self::isAdmin();
  }

  static function enforceDeleteRevision(): void {
    self::enforce(self::mayDeleteRevision());
  }

  static function mayDeleteRound(): bool {
    return self::isAdmin();
  }

  static function enforceDeleteRound(): void {
    self::enforce(self::mayDeleteRound());
  }

  static function enforceDeleteTask(Task $task): void {
    self::enforce($task->isDeletable());
  }

  static function mayDeleteTextblock(array $textblock): bool {
    // TODO: implement as $textblock->belongsToUser() once we migrate to OOP.
    $prefix = Config::USER_TEXTBLOCK_PREFIX;
    $belongsToUser = Str::startsWith($textblock['name'], $prefix);

    return $belongsToUser
      ? false
      : self::isAdmin();
  }

  static function enforceDeleteTextblock(array $textblock): void {
    self::enforce(self::mayDeleteTextblock($textblock));
  }

  static function enforceEditAttachmentIrreversibly(Attachment $attachment): void {
    self::enforce($attachment->isEditableIrreversibly());
  }

  static function enforceEditRound(array $round): void {
    self::enforce(self::ownsRound($round));
  }

  static function mayEditTags(): bool {
    return self::isAdmin();
  }

  static function enforceEditTags(): void {
    self::enforce(self::mayEditTags());
  }

  static function enforceEditTask(Task $task): void {
    self::enforce($task->isEditable());
  }

  static function enforceEditTaskRatings(Task $task): void {
    self::enforce($task->areRatingsEditable());
  }

  static function enforceEditTaskSecurity(Task $task): void {
    self::enforce($task->isSecurityEditable());
  }

  static function enforceEditTaskTags(Task $task): void {
    self::enforce($task->areTagsEditable());
  }

  static function mayEditTextblockReversibly(array $textblock): bool {
    $tb = Textblock::get_by_name($textblock['name']);
    return $tb->isEditableReversibly();
  }

  static function enforceEditTextblockReversibly(array $textblock): void {
    self::enforce(self::mayEditTextblockReversibly($textblock));
  }

  static function mayEditTextblockSecurity(): bool {
    return self::isAdmin();
  }

  static function enforceEditTextblockSecurity(): void {
    self::enforce(self::mayEditTextblockSecurity());
  }

  static function mayEditUserSecurity(): bool {
    return self::isAdmin();
  }

  static function enforceEditUserSecurity(): void {
    self::enforce(self::mayEditUserSecurity());
  }

  static function enforceLoggedIn(): void {
    self::enforce(self::isLoggedIn());
  }

  static function mayMoveTextblock(array $textblock): bool {
    // TODO: implement as $textblock->belongsToUser() once we migrate to OOP.
    $prefix = Config::USER_TEXTBLOCK_PREFIX;
    $belongsToUser = Str::startsWith($textblock['name'], $prefix);

    return $belongsToUser
      ? false
      : self::isAdmin();
  }

  static function enforceMoveTextblock(array $textblock): void {
    self::enforce(self::mayMoveTextblock($textblock));
  }

  static function mayReevalJobs(): bool {
    return self::isAdmin();
  }

  static function enforceReevalJobs(): void {
    self::enforce(self::mayReevalJobs());
  }

  static function mayRegisterForRound(array $round): bool {
    return
      self::isLoggedIn() &&
      ($round['state'] == 'waiting');
  }

  static function enforceRegisterForRound(array $round): void {
    self::enforce(self::mayRegisterForRound($round));
  }

  static function mayRunSpecialMacros(): bool {
    return self::isAdmin();
  }

  static function enforceRunSpecialMacros(): void {
    self::enforce(self::mayRunSpecialMacros());
  }

  static function maySkipJobs(): bool {
    return self::isAdmin();
  }

  static function enforceSkipJobs(): void {
    self::enforce(self::maySkipJobs());
  }

  static function mayTagRound(): bool {
    return
      self::isAdmin() ||
      self::isIntern();
  }

  static function mayTagTextblock(): bool {
    return self::isAdmin();
  }

  static function enforceTagTextblock(): void {
    self::enforce(self::mayTagTextblock());
  }

  static function mayTagUser(): bool {
    return self::isAdmin();
  }

  static function enforceTagUser(): void {
    self::enforce(self::mayTagUser());
  }

  static function mayUseTaskInRound(string $taskSecurity, string $roundType): bool {
    return
      self::isAdmin() ||
      ($taskSecurity == 'public') ||
      ($roundType != 'user-defined');
  }

  static function mayViewAcmRoundPartialScores(array $round): bool {
    return
      self::isAdmin() ||
      self::isIntern() ||
      ($round['state'] != 'waiting');
  }

  static function mayViewChanges(): bool {
    return self::isAdmin();
  }

  static function enforceViewChanges(): void {
    self::enforce(self::mayViewChanges());
  }

  static function mayViewIpAddresses(): bool {
    return
      self::isAdmin() ||
      self::isHelper();
  }

  static function enforceViewIpAddresses(): void {
    self::enforce(self::mayViewIpAddresses());
  }

  static function enforceViewJob(Job $job): void {
    self::enforce($job->isViewable());
  }

  static function enforceViewJobScore(Job $job): void {
    self::enforce($job->isScoreViewable());
  }

  static function enforceViewJobSource(Job $job): void {
    self::enforce($job->isSourceViewable());
  }

  static function mayViewRoundProgress(): bool {
    return
      self::isAdmin() ||
      self::isIntern();
  }

  static function mayViewRoundScores(array $round): bool {
    return
      self::isAdmin() ||
      self::isIntern() ||
      $round['public_eval'];
  }

  static function mayViewRoundTasks(array $round): bool {
    return
      self::isAdmin() ||
      self::isIntern() ||
      ($round['state'] != 'waiting');
  }

  static function enforceViewTask(Task $task): void {
    self::enforce($task->isViewable());
  }

  static function enforceViewTaskStats(Task $task): void {
    self::enforce($task->areStatsViewable());
  }

  static function mayViewTextblock(array $textblock): bool {
    return
      ($textblock['security'] != 'private') ||
      Identity::isAdmin();
  }

  static function enforceViewTextblock(array $textblock): void {
    self::enforce(self::mayViewTextblock($textblock));
  }

}
