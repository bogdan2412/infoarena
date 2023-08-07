<?php

class TestTaskViewLastScore extends FunctionalTest {

  function run(): void {
    $this->testAnonView();
    $this->testNormalView();
    $this->testAdminView();
  }

  private function testAnonView(): void {
    $this->ensureLoggedOut();
    $this->visitHomePage();
    $this->assertNoText('Scorul tău');
    $this->assertNoText('N/A');
  }

  private function testNormalView(): void {
    $this->login('normal', '1234');
    $this->visitHomePage();
    $this->assertTableCellText('table.tasks', 1, 6, 'N/A');
    $this->assertTableCellText('table.tasks', 2, 6, 'N/A');
  }

  private function testAdminView(): void {
    $this->login('admin', '1234');
    $this->visitHomePage();
    $this->assertTableCellText('table.tasks', 1, 6, '40');
    $this->assertTableCellText('table.tasks', 2, 6, 'N/A');
  }

}
