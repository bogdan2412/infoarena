<?php

class Core {

  const AUTOLOAD_PATHS = [
    'lib',
  ];

  static function autoload($className) {
    foreach (self::AUTOLOAD_PATHS as $path) {
      $filename = IA_ROOT_DIR . $path . '/' . $className . '.php';
      if (file_exists($filename)) {
        require_once $filename;
        return;
      }
    }
  }

  static function init() {
    spl_autoload_register('Core::autoload', true);
    FlashMessage::restoreFromSession();
    Smart::init();
  }

}
