<?php
/**
 * This script deletes cookies older than 30 days.
 **/

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../lib/Core.php';

$cutoff = strtotime('-30 days');

Model::factory('Cookie')
  ->where_lt('createDate', $cutoff)
  ->delete_many();
