<?php

class Base extends Model {
  const ACTION_SELECT = 1;
  const ACTION_SELECT_ALL = 2;
  const ACTION_DELETE_ALL = 3;
  const ACTION_COUNT = 4;

  const METHOD_PREFIXES = [
    'get_by_' => self::ACTION_SELECT,
    'get_all_by_' => self::ACTION_SELECT_ALL,
    'delete_all_by_' => self::ACTION_DELETE_ALL,
    'count_by_' => self::ACTION_COUNT,
  ];

  function __call($name, $arguments) {
    return $this->callHandler($name, $arguments);
  }

  static function __callStatic($name, $arguments) {
    return self::callHandler($name, $arguments);
  }

  // Handle calls like User::get_by_email($email) and User::get_all_by_email($email)
  static function callHandler(string $name, array $arguments) {
    foreach (self::METHOD_PREFIXES as $prefix => $action) {
      if (Str::startsWith($name, $prefix)) {
        $len = strlen($prefix);
        $fieldString = substr($name, $len);
        return self::resolve($fieldString, $arguments, $action);
      }
    }

    self::_die('cannot handle method', $name, $arguments);
  }

  private static function resolve(string $fieldString, array $arguments, int $action) {
    $fields = explode('_', $fieldString);
    if (count($fields) != count($arguments)) {
      self::_die('incorrect number of arguments', $action, $arguments);
    }
    $clause = Model::factory(get_called_class());
    foreach ($fields as $i => $field) {
      $clause = $clause->where($field, $arguments[$i]);
    }

    switch ($action) {
      case self::ACTION_SELECT: return $clause->find_one() ?: null;
      case self::ACTION_SELECT_ALL: return $clause->find_many();
      case self::ACTION_DELETE_ALL:
        $objects = $clause->find_many();
        foreach ($objects as $o) {
          $o->delete();
        }
        break;
      case self::ACTION_COUNT: return $clause->count();
    }
  }

  static function _die(string $error, string $name, array $arguments): void {
    printf("Error: %s in call to %s.%s, arguments: %s\n",
           $error, get_called_class(), $name, print_r($arguments, true));
    exit;
  }

  static function loadAndMapById(array $objectIds): array {
    $objects = Model::factory(get_called_class())
      ->where_in('id', $objectIds ?: [ -1 ])
      ->find_many();

    $results = [];
    foreach ($objects as $obj) {
      $results[$obj->id] = $obj;
    }
    return $results;
  }

  /**
   * Copies the values of all fields except id.
   * TODO: Can we just use PHP's clone()?
   **/
  function parisClone(): Model {
    $clone = Model::factory(get_called_class())->create();
    $fields = $this->as_array();
    foreach ($fields as $key => $value) {
      $clone->$key = $value;
    }
    return $clone;
  }

}
