<?php

class ScoreTaskTopUsers extends Base {

  public static $_table = 'ia_score_task_top_users';

  function getJob(): ?Job {
    return Job::get_by_id($this->job_id) ?: null;
  }

  function getRound(): ?Round {
    return Round::get_by_id($this->round_id) ?: null;
  }

  function getTask(): ?Task {
    return Task::get_by_id($this->task_id) ?: null;
  }

  function getUser(): ?User {
    return User::get_by_id($this->user_id) ?: null;
  }

}
