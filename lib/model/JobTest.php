<?php

class JobTest extends Base {

  public static $_table = 'ia_job_test';

  static function getAllForJob(int $jobId): array {
    return Model::factory('JobTest')
      ->where('job_id', $jobId)
      ->order_by_asc('test_group')
      ->order_by_asc('test_number')
      ->find_many();
  }

  function getTimeUsedMessage(): string {
    return ($this->grader_message == 'Time limit exceeded.')
      ? 'depășit'
      : ($this->exec_time . ' ms');
  }

  function getMemoryUsedMessage(): string {
    return ($this->grader_message == 'Memory limit exceeded.')
      ? 'depășită'
      : ($this->exec_time . ' kb');
  }

}
