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

  static function getUser(string $cookieVal): ?User {
    $cookie = Cookie::get_by_string($cookieVal);
    $user = $cookie
      ? (User::get_by_id($cookie->userId) ?: null)
      : null;
    return $user;
  }

}
