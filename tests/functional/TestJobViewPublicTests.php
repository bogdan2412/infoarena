<?php

class TestJobViewPublicTests extends FunctionalTest {

  const JOB_ID = 8;

  function run(): void {
    $this->testAnonView();
    $this->testNormalView();
    $this->testAdminView();
  }

  private function testAnonView(): void {
    $this->ensureLoggedOut();
    $this->visitJobPage(self::JOB_ID);
    $this->assertTableCellText('table.job', 4, 2, 'ascuns');
    $this->assertNoElement('table.job-eval-tests');
  }

  private function testNormalView(): void {
    $this->login('normal', '1234');
    $this->visitJobPage(self::JOB_ID);
    $this->assertTableCellText('table.job', 4, 2, 'ascuns');
    $this->assertTableRows('table.job-eval-tests', 2);
    $this->assertTableCellText('table.job-eval-tests', 1, 2, '1');
    $this->assertTableCellText('table.job-eval-tests', 2, 2, '3');
    $this->assertTextExists('Acesta este un raport parțial care include doar testele publice.');
  }

  private function testAdminView(): void {
    $this->login('admin', '1234');
    $this->visitJobPage(self::JOB_ID);
    $this->assertTableCellText('table.job', 4, 2, '70');
    $this->assertTableRows('table.job-eval-tests', 6);
    $this->assertTableCellText('table.job-eval-tests', 1, 2, '1');
    $this->assertTableCellText('table.job-eval-tests', 2, 2, '2');
    $this->assertNoText('Acesta este un raport parțial care include doar testele publice.');

    // Test grouping while we're at it.
    $numRowSpan2 = $this->countElementsByCss('table.job-eval-tests td[rowspan="2"]');
    $msg = sprintf('Expected 2 cells with colspan=2, found %d.', $numRowSpan2);
    $this->assert($numRowSpan2 == 2, $msg);
  }
}
