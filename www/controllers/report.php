<?php

function controller_report_list() {

  $reports = ReportUtil::getAllPositive();

  RecentPage::addCurrentPage('Rapoarte');
  Smart::assign([
    'reports' => $reports,
  ]);
  Smart::display('report/list.tpl');
}
