<?php

Core::init();

class Core {

  const AUTOLOAD_PATHS = [
    'lib',
    'lib/model',
    'lib/report',
  ];

  static function autoload($className) {
    foreach (self::AUTOLOAD_PATHS as $path) {
      $filename = Config::ROOT . $path . '/' . $className . '.php';
      if (file_exists($filename)) {
        require_once $filename;
        return;
      }
    }
  }

  static function init() {
    spl_autoload_register('Core::autoload', true);
    Time::init();
    DB::init();
    Session::init();
    FlashMessage::restoreFromSession();
    RecentPage::restoreFromSession();
    Smart::init();
  }

}
