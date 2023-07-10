<?php

class Task extends Base {

  public static $_table = 'ia_task';

  static function getValidSecurity(string $str): string {
    if (in_array($str, [ 'public', 'private', 'protected'])) {
      return $str;
    } else {
      return '';
    }
  }

  function getIncompleteRounds() {
    return Model::factory('Round')
      ->table_alias('r')
      ->select('r.*')
      ->join('ia_round_task', ['r.id', '=', 'rt.round_id'], 'rt')
      ->where('rt.task_id', $this->id)
      ->where_not_equal('r.type', 'archive')
      ->where_not_equal('r.state', 'complete')
      ->find_many();
  }

  function isPrivate(): bool {
    return $this->security == 'private';
  }

  // Returns true iff the current user owns the task.
  function isOwner(): bool {
    return User::getCurrentId() == $this->user_id;
  }

  // Returns true iff the current user can view details of this task.
  function isViewable(): bool {
    return
      !$this->isPrivate() ||
      $this->isOwner() ||
      User::isAdmin() ||
      User::isHelper() ||
      User::isIntern();
  }

}
