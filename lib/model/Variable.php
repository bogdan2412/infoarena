<?php

class Variable extends Base {

  public static $_table = 'ia_variable';

  static function peek(string $name, string $default = ''): string {
    $v = Variable::get_by_name($name);
    return $v ? $v->value : $default;
  }

  static function poke(string $name, string $value): void {
    $v = Variable::get_by_name($name);
    if (!$v) {
      $v = Model::factory('Variable')->create();
      $v->name = $name;
    }
    $v->value = $value;
    $v->save();
  }
}
