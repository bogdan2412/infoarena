<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../lib/Core.php';

db_connect();

$reports = ReportUtil::getAll();

foreach ($reports as $report) {
  $report->updateCount();
}
