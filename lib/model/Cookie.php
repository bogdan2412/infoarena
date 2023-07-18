<?php

class Cookie extends Base {

  static function create($userId): Cookie {
    $c = Model::factory('Cookie')->create();
    $c->userId = $userId;
    $c->string = Str::randomString(40);
    $c->createDate = time();
    $c->save();
    return $c;
  }

}
