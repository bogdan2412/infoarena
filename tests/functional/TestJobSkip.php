<?php

class TestJobSkip extends FunctionalTest {

  function run(): void {
    $this->testInternCannotSkip();
    $this->testAdminCanSkip();
  }

  private function testInternCannotSkip(): void {
    $this->login('intern', '1234');
    $this->visitMonitorPage();
    $this->assertNoLink('ignoră');
    $this->assertNoElement('input[value="Ignoră joburile selectate"]');
  }

  private function testAdminCanSkip(): void {
    $this->login('admin', '1234');
    $this->visitMonitorPage();

    $links = $this->getLinksByText('ignoră');
    $msg = sprintf('Expected 7 ignore links, found %d.', count($links));
    $this->assert(count($links) == 8, $msg);

    $this->getElementByCss('input[value="Ignoră joburile selectate"]');
  }

}
