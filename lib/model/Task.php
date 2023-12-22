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

  function getMethodsAndAlgorithms(): array {
    $methods = $this->getTags('method');
    $algorithms = $this->getTags('algorithm');

    $results = [];
    foreach ($methods as $method) {
      $arr = array_filter($algorithms, function(Tag $x) use ($method) {
        return $x->parent == $method->id;
      });

      $results[] = [
        'method' => $method,
        'algorithms' => $arr,
      ];
    }

    return $results;
  }

  function getTags(string $tagType): array {
    return Model::factory('Tag')
      ->table_alias('tag')
      ->select('tag.*')
      ->join('ia_task_tags', [ 'task_tag.tag_id', '=', 'tag.id' ], 'task_tag')
      ->where('tag.type', $tagType)
      ->where('task_tag.task_id', $this->id)
      ->order_by_asc('tag.name')
      ->find_many();
  }

  function getLargestInputFile(): int {
    $obj = Model::factory('Attachment')
      ->select_expr('max(size)', 'maxSize')
      ->where('page', $this->page_name)
      ->where_like('name', 'grader_test%.in')
      ->find_one();

    return $obj->maxSize ?? 0;
  }

  function isInAnyRunningRounds(): bool {
    $numRunningRounds = Model::factory('Round')
      ->table_alias('r')
      ->join('ia_round_task', [ 'r.id', '=', 'rt.round_id' ], 'rt')
      ->where('rt.task_id', $this->id)
      ->where('r.state', 'running')
      ->count();

    return ($numRunningRounds > 0);
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

  static function loadByRoundId(string $roundId) {
    return Model::factory('Task')
      ->table_alias('t')
      ->select('t.*')
      ->join('ia_round_task', ['t.id', '=', 'rt.task_id'], 'rt')
      ->where('rt.round_id', $roundId)
      ->order_by_asc('rt.order_id')
      ->find_many();
  }

  function isPrivate(): bool {
    return $this->security == 'private';
  }

  function isPublic(): bool {
    return $this->security == 'public';
  }

  function isViewable(): bool {
    return
      !$this->isPrivate() ||
      Identity::ownsTask($this);
  }

  function areStatsViewable(): bool {
    return
      $this->isPublic() ||
      Identity::ownsTask($this);
  }

  function isLastScoreViewable(): bool {
    return
      $this->isPublic() ||
      Identity::ownsTask($this);
  }

  function areTagsViewable(): bool {
    return
      $this->isPublic() ||
      Identity::ownsTask($this);
  }

  function areGraderAttachmentsViewable(): bool {
    return
      ($this->isPublic() && $this->open_tests) ||
      Identity::ownsTask($this);
  }

  function isEditable(): bool {
    return Identity::ownsTask($this);
  }

  function areRatingsEditable(): bool {
    return Identity::ownsTask($this);
  }

  function areTagsEditable(): bool {
    return Identity::ownsTask($this);
  }

  function isAuthorEditable(): bool {
    return Identity::isAdmin();
  }

  function isSecurityEditable(): bool {
    return Identity::isAdmin();
  }

  function isOpenEditable(): bool {
    return Identity::isAdmin();
  }

  function isDeletable(): bool {
    return Identity::ownsTask($this);
  }

  function canSubmit(): bool {
    if (Identity::ownsTask($this)) {
      return true;
    }

    return
      Identity::isLoggedIn() &&
      !$this->isPrivate() &&
      $this->isInAnyRunningRounds();
  }

}
