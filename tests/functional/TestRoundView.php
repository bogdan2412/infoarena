<?php

class TestRoundView extends FunctionalTest {

  function run(): void {
    $this->testAnonViewArchive();
    $this->testAnonViewClassic();
    $this->testInternViewClassic();
  }

  private function testAnonViewArchive(): void {
    $this->ensureLoggedOut();
    $this->visitRoundPage('round-archive');
    $this->assertTextExists('Task 1');
    $this->assertTextExists('Task 2');
  }

  private function testAnonViewClassic(): void {
    $this->ensureLoggedOut();
    $this->visitRoundPage('round-classic');
    $this->assertNoText('Task 1');
    $this->assertNoText('Task 2');
  }

  private function testInternViewClassic(): void {
    $this->login('intern', '1234');
    $this->visitRoundPage('round-classic');
    $this->assertTextExists('Task 1');
    $this->assertTextExists('Task 2');
  }
}
