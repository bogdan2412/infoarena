<?php

require_once __DIR__ . '/../../eval/config.php';
require_once __DIR__ . '/../../common/common.php';

spl_autoload_register(function($className) {
  $fileName = sprintf('%s/%s.php', __DIR__, $className);
  if (file_exists($fileName)) {
    require_once $fileName;
  }
});

$main = new Main();
try {
  $main->run();
} catch (BException $e) {
  Log::fatal($e->getMessage(), $e->getArgs());
}
