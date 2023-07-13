<?php

class Job extends Base {

  public static $_table = 'ia_job';

  function getRound(): ?Round {
    return Round::get_by_id($this->round_id) ?: null;
  }

  function getTask(): ?Task {
    return Task::get_by_id($this->task_id);
  }

  function getUser(): ?User {
    return User::get_by_id($this->user_id);
  }

  function getSizeString(): string {
    $len = strlen($this->file_contents);
    return sprintf('%.2f kb', $len / 1024);
  }

  function getStatusMessage(): string {
    switch ($this->status) {
      case 'skipped': return 'job ignorat';
      case 'waiting': return 'în așteptare';
      case 'processing': return 'în curs de evaluare';
    }

    // 'done'
    $msg = $this->eval_message;

    if ($this->isScoreViewable()) {
      return sprintf('%s: %s puncte', $msg, $this->score);
    } else if ($this->isPartialFeedbackViewable()) {
      return sprintf('%s: rezultate parțiale disponibile', $msg);
    } else {
      return $msg;
    }
  }

  // Returns true iff the current user owns the job.
  function isOwner(): bool {
    return User::getCurrentId() == $this->user_id;
  }

  function isPartialFeedbackViewable(): bool {
    $task = $this->getTask();
    if (!$task->isViewable() || !$task->public_tests) {
      return false;
    }

    return
      $this->isOwner() ||
      User::isAdmin() ||
      User::isIntern();
  }

  function isScoreViewable(): bool {
    $task = $this->getTask();
    if (!$task->isViewable()) {
      return false;
    }

    $round = $this->getRound();
    if ($round && $round->public_eval) {
      return true;
    }

    return
      $task->isOwner() ||
      User::isAdmin() ||
      User::isIntern();
  }

  function isSourceViewable(): bool {
    if (User::isAdmin()) {
      return true;
    }

    if ($this->isOwner()) {
      return true;
    }

    $task = $this->getTask();
    if ($task->isOwner()) {
      return true;
    }

    if ($task->isPrivate()) {
      return false;
    }

    $incompleteRounds = $task->getIncompleteRounds();
    if (count($incompleteRounds)) {
      return false;
    }

    if ($task->open_source) {
      return true;
    }

    return false;
  }

}
