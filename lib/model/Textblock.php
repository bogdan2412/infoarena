<?php

require_once __DIR__ . '/../../common/db/round.php';

class Textblock extends Base {

  public static $_table = 'ia_textblock';

  static function create(string $name, string $security): Textblock {
    $tb = Model::factory('Textblock')->create();
    $tb->name = $name;
    $tb->title = $name;
    $tb->text = 'Scrie aici despre ' . $name;
    $tb->security = $security;
    $tb->user_id = Identity::getId();
    return $tb;
  }

  function isPrivate(): bool {
    return $this->security == 'private';
  }

  private function belongsToRound(): bool {
    return Str::isRoundPage($this->name);
  }

  private function belongsToTask(): bool {
    return Str::isTaskPage($this->name);
  }

  private function belongsToUser(): bool {
    return Str::isUserPage($this->name);
  }

  private function getSubject(): string {
    $parts = explode('/', $this->name);
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

  function isEditableReversibly(): bool {
    if ($this->belongsToUser()) {
      $user = $this->getUser();
      return $user && $user->isEditable();
    }

    if ($this->belongsToTask()) {
      $task = $this->getTask();
      return $task && Identity::ownsTask($task);
    }

    if ($this->belongsToRound()) {
      $round = $this->getRound();
      return $round && Identity::ownsRound($round);
    }

    return
      Identity::isAdmin() ||
      (Identity::isHelper() && ($this->security == 'public'));
  }

  function isViewable(): bool {
    if ($this->belongsToUser()) {
      return true;
    }

    if ($this->belongsToTask()) {
      $task = $this->getTask();
      return $task && $task->isViewable();
    }

    if ($this->belongsToRound()) {
      return true;
    }

    return
      ($this->security != 'private') ||
      Identity::isAdmin();
  }

}
