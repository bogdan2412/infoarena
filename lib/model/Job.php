<?php

class Job extends Base {

  const SOURCE_VISIBILITY_UNKNOWN = 0;
  const SOURCE_VISIBILITY_NO = 1;
  const SOURCE_VISIBILITY_FORCE = 2;
  const SOURCE_VISIBILITY_YES = 3;

  public static string $_table = 'ia_job';
  private static ?Round $round = null;
  private int $sourceVisibility = self::SOURCE_VISIBILITY_UNKNOWN;

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
    $params = round_get_parameters($round->id);
    $total->limit($params['minimum_score']);

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
    $description = sprintf('%d%% (pentru %.1f minute)', $amount, $minutes);
    return new JobPenalty($amount, $description);
  }

  function getSubmissionPenalty(): JobPenalty {
    if (!$this->submissions) {
      return new JobPenalty(0, '');
    }

    $params = round_get_parameters($this->round_id);
    $unitCost = $params['submit_cost'];
    $amount = $this->submissions * $unitCost;
    $description = sprintf('%d%% (pentru %d submisie/ii)', $amount, $this->submissions);
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

  private function getSourceVisibility(): int {
    if ($this->sourceVisibility == self::SOURCE_VISIBILITY_UNKNOWN) {
      $this->sourceVisibility = $this->computeSourceVisibility();
    }
    return $this->sourceVisibility;
  }

  private function computeSourceVisibility(): int {
    if (Identity::getId() == $this->user_id) {
      return self::SOURCE_VISIBILITY_YES;
    }

    $task = $this->getTask();
    if (Identity::ownsTask($task)) {
      return self::SOURCE_VISIBILITY_YES;
    }

    if ($task->isPrivate()) {
      return self::SOURCE_VISIBILITY_NO;
    }

    $incompleteRounds = $task->getIncompleteRounds();
    if (count($incompleteRounds)) {
      return self::SOURCE_VISIBILITY_NO;
    }

    if ($task->open_source) {
      return self::SOURCE_VISIBILITY_YES;
    }

    $me = Identity::getId();
    if (!$me) {
      return self::SOURCE_VISIBILITY_NO;
    }

    if (task_user_has_solved($task->id, $me)) {
      return self::SOURCE_VISIBILITY_YES;
    }

    if (TaskPeep::exists($me, $task->id)) {
      return self::SOURCE_VISIBILITY_YES;
    }

    return self::SOURCE_VISIBILITY_NO;
  }

  function isSourceViewable(): bool {
    return $this->getSourceVisibility() != self::SOURCE_VISIBILITY_NO;
  }

  function needsToForceViewSource(): bool {
    return $this->getSourceVisibility() == self::SOURCE_VISIBILITY_FORCE;
  }

  static function countByRoundId(string $roundId): int {
    return Model::factory('Job')
      ->where('round_id', $roundId)
      ->count();
  }

  static function countUserRoundTaskSubmissions(
    int $userId, string $roundId, string $taskId): int {
    return Model::factory('Job')
      ->where('user_id', $userId)
      ->where('round_id', $roundId)
      ->where('task_id', $taskId)
      ->count();
  }

  static function deleteById(string $jobId): void {
    $job = Job::get_by_id($jobId);
    if (!$job) {
      FlashMessage::addError('Job inexistent.');
      Util::redirectToHome();
    }
    $job->delete();
  }

  function delete(): void {
    Model::factory('JobTest')
      ->where('job_id', $this->id)
      ->delete_many();

    Model::factory('TaskTop')
      ->where('job_id', $this->id)
      ->delete_many();

    parent::delete();
  }

}
