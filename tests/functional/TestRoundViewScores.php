<?php

class TestRoundViewScores extends FunctionalTest {

  function run(): void {
    $this->testAnonViewPublic();
    $this->changeRoundPublicEval('Nu');
    $this->testNormalViewPrivate();
    $this->testInternViewPrivate();
    $this->changeRoundPublicEval('Da');
  }

  private function testAnonViewPublic(): void {
    $this->ensureLoggedOut();
    $this->visitRoundResultsPage('round-classic');
    $this->assertTableCellText('table.alternating-colors', 1, 5, '30');
  }

  private function testNormalViewPrivate(): void {
    $this->login('normal', '1234');
    $this->visitRoundResultsPage('round-classic');
    $this->assertNoElement('table.alternating-colors');
    $this->assertTextExists('Nici un rezultat înregistrat pentru această rundă.');
  }

  private function testInternViewPrivate(): void {
    $this->login('intern', '1234');
    $this->visitRoundResultsPage('round-classic');
    $this->assertTableCellText('table.alternating-colors', 1, 5, '30');
  }

  private function changeRoundPublicEval(string $yesOrNo): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage('round-classic');
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_public_eval', $yesOrNo);
    $this->clickButton('Salvează');
  }

}
