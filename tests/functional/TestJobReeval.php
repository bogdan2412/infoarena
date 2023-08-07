<?php

class TestJobReeval extends FunctionalTest {

  function run(): void {
    $this->testInternCannotReeval();
    $this->testAdminCanReeval();
  }

  private function testInternCannotReeval(): void {
    $this->login('intern', '1234');
    $this->visitMonitorPage();
    $this->assertNoText('Re-evaluează!');
  }

  private function testAdminCanReeval(): void {
    $this->login('admin', '1234');
    $this->visitMonitorPage();
    $this->getElementByCss('input[value="Re-evaluează!"]');
  }

}
