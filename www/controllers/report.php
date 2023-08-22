<?php

function controller_report_list(): void {

  $reports = ReportUtil::getAllPositive();

  RecentPage::addCurrentPage('Rapoarte');
  Smart::assign([
    'reports' => $reports,
  ]);
  Smart::display('report/list.tpl');
}

function controller_report_view(string $reportName): void {

  // FIXME

  RecentPage::addCurrentPage('Rapoarte');
  Smart::assign([
    'reports' => [],
  ]);
  Smart::display('report/list.tpl');
}
