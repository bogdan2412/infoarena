<?php

require_once __DIR__ . '/../../eval/config.php';
require_once 'AnsiColors.php';
require_once 'Args.php';
require_once 'BException.php';
require_once 'Checkpointer.php';
require_once 'Choice.php';
require_once 'Database.php';
require_once 'JobBenchmark.php';
require_once 'Log.php';
require_once 'Main.php';
require_once 'TaskBenchmark.php';

$main = new Main();
try {
  $main->run();
} catch (BException $e) {
  Log::fatal($e->getMessage(), $e->getArgs());
}
