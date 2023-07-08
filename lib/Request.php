<?php

class Request {

  static function isWeb(): bool {
    return php_sapi_name() != 'cli';
  }

}
