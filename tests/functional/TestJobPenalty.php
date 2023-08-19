<?php

class TestJobPenalty extends FunctionalTest {

  function run(): void {
    $this->testAnonView();
  }

  private function testAnonView(): void {
    $this->ensureLoggedOut();
    $this->login('normal', '1234');

    $this->assertPenaltyMessage(
      9,
      'Penalizare: 11% (pentru 1438.5 minute)',
      11);

    $this->assertPenaltyMessage(
      10,
      'Penalizare: 11% (pentru 1438.5 minute) + 10% (pentru 1 submisie/ii)',
      21);

    $this->assertPenaltyMessage(
      11,
      'Penalizare: 11% (pentru 1438.5 minute) + 20% (pentru 2 submisie/ii), limitat la 25%',
      25);

    $this->assertPenaltyMessage(
      12,
      'Penalizare: 11% (pentru 1438.5 minute) + 30% (pentru 3 submisie/ii), limitat la 25%',
      25);
  }

  private function assertPenaltyMessage(int $jobId, string $message, int $percent): void {
    $this->visitJobPage($jobId);
    $this->assertTableCellText('table.job-eval-tests', 6, 1, $message);
    $this->assertTableCellText('table.job-eval-tests', 6, 2, $percent . '%');
  }

}
