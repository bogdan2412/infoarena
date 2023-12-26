<?php

/**
 * System tools.
 **/

class OS {

  static function execute($command, &$output) {
    exec($command, $output, $exitCode);
    $output = implode("\n", $output);
    return $exitCode;
  }

}
