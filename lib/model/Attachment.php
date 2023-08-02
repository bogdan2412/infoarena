<?php

class Attachment extends Base {

  public static $_table = 'ia_file';

  static function getDirectory(): string {
    return Config::TESTING_MODE
      ? '/tmp/attach-test/'
      : Config::ROOT . 'attach/';
  }

}
