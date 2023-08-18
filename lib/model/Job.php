<?php

class Job extends Base {

  public static string $_table = 'ia_job';
  private static ?Round $round = null;

  function getRound(): ?Round {
    if (!$this->round) {
      $this->round = Round::get_by_id($this->round_id) ?: null;
    }
    return $this->round;
  }

  function getTask(): ?Task {
    return Task::get_by_id($this->task_id);
  }

  function getUser(): ?User {
    return User::get_by_id($this->user_id);
  }

  function getSizeString(): string {
    $len = strlen($this->file_contents ?? '');
    return sprintf('%.2f kb', $len / 1024);
  }

  function getShortStatusMessage(): string {
    switch ($this->status) {
      case 'skipped': return 'job ignorat';
      case 'waiting': return 'în așteptare';
      case 'processing': return 'în curs de evaluare';
      case 'done': return 'evaluat';
      default: return 'necunoscută';
    }
  }

  function getStatusMessage(): string {
    if (!$this->isDone()) {
      return $this->getShortStatusMessage();
    }

    $msg = $this->eval_message;

    if ($this->isScoreViewable()) {
      return sprintf('%s: %s puncte', $msg, $this->score);
    } else if ($this->isPartialFeedbackViewable()) {
      return sprintf('%s: rezultate parțiale disponibile', $msg);
    } else {
      return $msg;
    }
  }

  function isDone(): bool {
    return $this->status == 'done';
  }

  function getPenalty(): JobPenalty {
    $round = $this->getRound();
    if (!$round || ($round->type != 'penalty-round')) {
      return new JobPenalty(0, '');
    }

    $timePenalty = $this->getTimePenalty();
    $submissionPenalty = $this->getSubmissionPenalty();
    $total = $timePenalty->add($submissionPenalty);
    return $total;
  }

  function getTimePenalty(): JobPenalty {
    $round = $this->getRound();
    $params = round_get_parameters($round->id);
    $roundTime = db_date_parse($round->start_time);
    $jobTime = db_date_parse($this->submit_time);
    $decay = $params['decay_period'];
    $amount = (int)(($jobTime - $roundTime) / $decay);

    if ($amount <= 0) {
      return new JobPenalty(0, '');
    }

    $minutes = ($jobTime - $roundTime) / 60;
    $description = sprintf('%d (pentru %.1f minute)', $amount, $minutes);
    return new JobPenalty($amount, $description);
  }

  function getSubmissionPenalty(): JobPenalty {
    if (!$this->submissions) {
      return new JobPenalty(0, '');
    }

    $params = round_get_parameters($this->round_id);
    $unitCost = $params['submit_cost'];
    $amount = $this->submissions * $unitCost;
    $description = sprintf('%d (pentru %d submisie/ii)', $amount, $this->submissions);
    return new JobPenalty($amount, $description);
  }

  function isPartialFeedbackViewable(): bool {
    $task = $this->getTask();
    if (!$task->isViewable() || !$task->public_tests) {
      return false;
    }

    return Identity::ownsJob($this);
  }

  function isViewable(): bool {
    $task = $this->getTask();
    return
      !$task->isPrivate() ||
      Identity::ownsTask($task);
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

    return Identity::ownsTask($task);
  }

  function isSourceViewable(): bool {
    if (Identity::getId() == $this->user_id) {
      return true;
    }

    $task = $this->getTask();
    if (Identity::ownsTask($task)) {
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
