<?php

require_once __DIR__ . '/../../common/avatar.php';

class Attachment extends Base {

  const AVATAR_SIZES = [ 'tiny', 'small', 'normal', 'big', 'full' ];

  public static $_table = 'ia_file';

  static function getDirectory(): string {
    return Config::TESTING_MODE
      ? '/tmp/attach-test/'
      : Config::ROOT . 'attach/';
  }

  static function normalizeAndGetByNamePage(string $name, string $page): ?Attachment {
    $page = normalize_page_name($page);

    return Attachment::get_by_name_page($name, $page) ?: null;
  }

  private function isTaskGrader(): bool {
    return
      Str::startsWith($this->name, 'grader_') &&
      $this->belongsToTask();
  }

  private function belongsToRound(): bool {
    return Str::isRoundPage($this->page);
  }

  private function belongsToTask(): bool {
    return Str::isTaskPage($this->page);
  }

  private function belongsToUser(): bool {
    return Str::isUserPage($this->page);
  }

  private function getSubject(): string {
    $parts = explode('/', $this->page);
    return $parts[1] ?? '';
  }

  private function getRound(): array {
    $roundId = $this->getSubject();
    return round_get($roundId) ?? [];
  }

  private function getTask(): ?Task {
    $taskId = $this->getSubject();
    return Task::get_by_id($taskId) ?: null;
  }

  function getUser(): ?User {
    $username = $this->getSubject();
    return User::get_by_username($username) ?: null;
  }

  static function getAvatarDirectories(): array {
    $results = [];
    foreach (self::AVATAR_SIZES as $size) {
      $results[] = Config::AVATAR_DIR . $size . '/';
    }
    return $results;
  }

  function getAvatarFiles(): array {
    $results = [];

    if ($this->belongsToUser()) {
      $user = $this->getUser();
      if ($user) {
        foreach (self::getAvatarDirectories() as $dir) {
          $results[] = $dir . 'a' . $user->username;
        }
      }
    }

    return $results;
  }

  private function getTextblock(): Textblock {
    return Textblock::get_by_name($this->page);
  }

  function isViewable(): bool {
    if ($this->belongsToUser()) { // in particular, avatar images
      return true;
    }

    if ($this->belongsToRound()) {
      return true;
    }

    if ($this->belongsToTask()) {
      $task = $this->getTask();
      return ($this->isTaskGrader())
        ? $task->areGraderAttachmentsViewable()
        : $task->isViewable();
    }

    // Otherwise it belongs to a textblock.
    $tb = $this->getTextblock();
    return
      !$tb->isPrivate() ||
      Identity::isAdmin();
  }

  function isEditableIrreversibly(): bool {
    if ($this->belongsToUser())  {
      $user = $this->getUser();
      return $user && $user->isEditable();
    }

    if ($this->belongsToTask()) {
      $task = $this->getTask();
      return $task && Identity::ownsTask($task);
    }

    if ($this->belongsToRound()) {
      return
        Identity::isAdmin() ||
        Identity::isIntern();
    }

    return Identity::isAdmin();
  }

  static function deleteById($id): void {
    $att = Attachment::get_by_id($id);
    if (!$att) {
      FlashMessage::addError('Fișier inexistent.');
      Util::redirectToHome();
    }
    $att->delete();
  }

  function delete(): void {
    attachment_delete_by_id($this->id);
    if (is_avatar_attachment($this->name, $this->page)) {
      $matches = get_page_user_name($this->page);
      avatar_delete($matches[1]);
    }
  }

}
