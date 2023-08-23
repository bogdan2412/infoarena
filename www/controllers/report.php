<?php

require_once __DIR__ . '/../../common/db/round.php';

function controller_report_list(): void {

  Identity::enforceViewReports();
  $reports = ReportUtil::getAllPositive();

  RecentPage::addCurrentPage('Rapoarte');
  Smart::assign([
    'reports' => $reports,
  ]);
  Smart::display('report/list.tpl');
}

function controller_report_view(string $reportUrlName): void {

  Identity::enforceViewReports();
  $report = ReportUtil::getByUrlName($reportUrlName);

  if (!$report) {
    FlashMessage::addWarning('Raportul cerut nu există.');
    Util::redirectToHome();
  }

  if (Request::has('report_action')) {
    $report->action();
  }

  RecentPage::addCurrentPage($report->getDescription());
  Smart::assign([
    'report' => $report,
  ]);
  Smart::display('report/view.tpl');
}
