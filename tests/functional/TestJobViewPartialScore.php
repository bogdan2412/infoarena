<?php

class TestJobViewPartialScore extends FunctionalTest {

  const HELPER_JOB_ID = 6;

  function run(): void {
    $this->testNormalViewPublic();
    $this->changeRoundPublicEval('Nu');
    $this->testNormalViewPrivate();
    $this->testHelperViewPrivate();
    $this->testAdminViewPrivate();
    $this->changeRoundPublicEval('Da');
  }

  private function testNormalViewPublic(): void {
    $this->login('normal', '1234');
    $this->visitJobPage(self::HELPER_JOB_ID);
    $this->assertTableCellText('table.job-eval-tests', 5, 5, '12');
  }

  private function testNormalViewPrivate(): void {
    $this->login('normal', '1234');
    $this->visitJobPage(self::HELPER_JOB_ID);
    $this->assertTableCellText('table.job', 4, 2, 'Ascuns');
    $this->assertNoText('Timp execuție');
  }

  private function testHelperViewPrivate(): void {
    $this->login('helper', '1234');
    $this->visitJobPage(self::HELPER_JOB_ID);
    $this->assertTableCellText('table.job', 4, 2, 'Ascuns');

    $numRows = $this->countElementsByCss('table.job-eval-tests tbody tr');
    $msg = sprintf('Expected 2 feedback rows, found %d.', $numRows);
    $this->assert($numRows == 2, $msg);
  }

  private function testAdminViewPrivate(): void {
    $this->login('admin', '1234');
    $this->visitJobPage(self::HELPER_JOB_ID);
    $this->assertTableCellText('table.job', 4, 2, '60');

    $numRows = $this->countElementsByCss('table.job-eval-tests tbody tr');
    $msg = sprintf('Expected 6 feedback rows, found %d.', $numRows);
    $this->assert($numRows == 6, $msg);
  }

  private function changeRoundPublicEval(string $yesOrNo): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage('round-archive');
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_public_eval', $yesOrNo);
    $this->clickButton('Salvează');
  }

}
