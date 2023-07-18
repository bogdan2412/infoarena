<?php

class Attachment extends Base {

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

  private function getUser(): ?User {
    $username = $this->getSubject();
    return User::get_by_username($username) ?: null;
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

}
