<?php

class TestJobViewScore extends FunctionalTest {

  function run(): void {
    $this->testAnonView();
    $this->testHelperView();
    $this->testAdminView();
  }

  private function testAnonView(): void {
    $this->ensureLoggedOut();
    $this->visitMonitorPage();
    $this->assertNoText('Evaluare completă: 10 puncte');
    $this->assertNoText('Evaluare completă: 20 puncte');
    $this->assertTextExists('Evaluare completă: 30 puncte');
    $this->assertTextExists('Evaluare completă: 40 puncte');
    $this->assertTextExists('Evaluare completă: 50 puncte');
    $this->assertTextExists('Evaluare completă: 60 puncte');
    $this->assertNoText('Evaluare completă: 70 puncte');
    $this->assertScoreOnJobDetailPage(1, 'ascuns');
    $this->assertScoreOnJobDetailPage(3, '30');
    $this->assertScoreOnJobDetailPage(7, 'ascuns');
  }

  private function testHelperView(): void {
    // Additionally, helper can view the score for job 2 because she owns task2.
    $this->login('helper', '1234');
    $this->visitMonitorPage();
    $this->assertNoText('Evaluare completă: 10 puncte');
    $this->assertTextExists('Evaluare completă: 20 puncte');
    $this->assertScoreOnJobDetailPage(1, 'ascuns');
    $this->assertScoreOnJobDetailPage(2, '20');
  }

  private function testAdminView(): void {
    // Additionally, admin can view the score for job 1.
    $this->login('admin', '1234');
    $this->visitMonitorPage();
    $this->assertTextExists('Evaluare completă: 10 puncte');
    $this->assertScoreOnJobDetailPage(1, '10');
    $this->testTimeAndMemory(1);
  }

  private function assertScoreOnJobDetailPage(int $jobId, string $expectedScore): void {
    $this->visitJobPage($jobId);
    $this->assertTableCellText('table.job', 4, 2, $expectedScore);
  }

  private function testTimeAndMemory(int $jobId): void {
    $this->visitJobPage($jobId);
    for ($row = 1; $row <= 5; $row++) {
      $this->assertTableCellText('table.job-eval-tests', $row, 2, '100 ms');
      $this->assertTableCellText('table.job-eval-tests', $row, 3, '500 kb');
    }
  }

}
