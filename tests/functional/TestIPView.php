<?php

class TestIPView extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotViewTextblockIP();
    $this->testNormalCannotViewTextblockIP();
    $this->testInternCanViewTextblockIP();
  }

  private function testAnonCannotViewTextblockIP(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockHistoryPage('page-public');
    $this->assertNoText('42.42.42.42');
  }

  private function testNormalCannotViewTextblockIP(): void {
    $this->login('normal', '1234');
    $this->visitTextblockHistoryPage('page-public');
    $this->assertNoText('42.42.42.42');
  }

  private function testInternCanViewTextblockIP(): void {
    $this->login('intern', '1234');
    $this->visitTextblockHistoryPage('page-public');
    $this->assertTextExists('42.42.42.42');
  }
}
