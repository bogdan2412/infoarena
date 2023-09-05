<?php

class ReportJobTestsBadJob extends Report {

  function getDescription(): string {
    return 'Teste pentru joburi inexistente';
  }

  function getVariable(): string {
    return 'Count.jobTestsBadJob';
  }

  function getTemplateName(): string {
    return 'report/jobTestList.tpl';
  }

  function getSupportedActions(): array {
    return [ 'cleanup' ];
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
