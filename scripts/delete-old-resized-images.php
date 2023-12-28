<?php
/**
 * This script deletes resized images older than 30 days.
 **/

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../lib/Core.php';

$files = glob(Image::RESIZED_PATH . '*');
$cutoff = strtotime('-30 days');
  
foreach ($files as $file) {
  if (is_file($file) &&
      (filemtime($file) < $cutoff)) {
    unlink($file);
  }
}
