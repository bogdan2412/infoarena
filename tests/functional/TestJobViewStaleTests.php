<?php

class TestJobViewStaleTests extends FunctionalTest {

  function run(): void {
    $this->login('admin', '1234');
    $this->changeTestParams('8', '');
    $this->testIncreasedTestCount();
    $this->changeTestParams('3', '');
    $this->testDecreasedTestCount();
    $this->changeTestParams('5', '1-2;3-5');
    $this->testChangedGroups();
    $this->changeTestParams('5', ''); // back to reality
  }

  private function changeTestParams(string $testCount, string $testGroups): void {
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Parametri');
    $this->changeInput('#form_test_count', $testCount);
    $this->changeInput('#form_test_groups', $testGroups);
    $this->clickButton('Salvează');
  }

  private function testIncreasedTestCount(): void {
    $this->visitJobPage(6);
    $this->assertNoText('Testul 5 nu există.');
    $this->assertTextExists('Testul 6 nu există.');
    $this->assertTextExists('Testul 7 nu există.');
    $this->assertTextExists('Testul 8 nu există.');
  }

  private function testDecreasedTestCount(): void {
    $this->visitJobPage(6);
    $this->assertTextExists('Testele 4, 5 au numere incorecte (problema are 3 teste).');
  }
  
  private function testChangedGroups(): void {
    $this->visitJobPage(6);
    $this->assertTextExists('Testul 2 figurează în grupul 2, problema îl pune în grupul 1.');
    $this->assertTextExists('Testul 3 figurează în grupul 3, problema îl pune în grupul 2.');
    $this->assertTextExists('Testul 4 figurează în grupul 4, problema îl pune în grupul 2.');
    $this->assertTextExists('Testul 5 figurează în grupul 5, problema îl pune în grupul 2.');
  }
  
}
