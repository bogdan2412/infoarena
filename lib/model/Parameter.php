<?php

class Parameter extends Base {

  public static $_table = 'ia_parameter_value';

  static function getTaskParameter(string $taskId, string $name) {
    return Model::factory('Parameter')
      ->where('object_type', 'task')
      ->where('object_id', $taskId)
      ->where('parameter_id', $name)
      ->find_one();
  }

}
