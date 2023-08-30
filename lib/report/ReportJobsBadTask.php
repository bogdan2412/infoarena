<?php

class ReportJobsBadTask extends Report {

  function getDescription(): string {
    return 'Joburi pentru probleme inexistente';
  }

  function getVariable(): string {
    return 'Count.jobsBadTask';
  }

  function getTemplateName(): string {
    return 'report/jobList.tpl';
  }

  function getSupportedActions(): array {
    return [ 'job_delete' ];
  }

  function buildQuery(): ORM {
    return Model::factory('Job')
      ->table_alias('j')
      ->select('j.id')
      ->left_outer_join('ia_task', [ 'j.task_id', '=', 't.id' ], 't')
      ->where_null('t.id');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getJobs(): array {
    $query = $this->buildQuery();
    $ids = $query->find_array();

    $jobs = array_map(function(array $rec): Job {
      return Job::get_by_id($rec['id']);
    }, $ids);

    return $jobs;
  }

}
