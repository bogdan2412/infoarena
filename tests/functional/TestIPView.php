<?php

class TestIPView extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotViewTextblockIP();
    $this->testAnonCannotViewAttachmentIP();
    $this->testAnonCannotViewJobIP();

    $this->testNormalCannotViewTextblockIP();
    $this->testNormalCannotViewAttachmentIP();
    $this->testNormalCannotViewJobIP();

    $this->testHelperCanViewTextblockIP();
    $this->testHelperCanViewAttachmentIP();
    $this->testHelperCanViewJobIP();
  }

  private function testAnonCannotViewTextblockIP(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockHistoryPage('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 5, 'N/A');
  }

  private function testAnonCannotViewAttachmentIP(): void {
    $this->visitAttachmentList('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 7, 'N/A');
  }

  private function testAnonCannotViewJobIP(): void {
    $this->visitJobPage(1);
    $this->assertNoText('42.42.42.42');
  }

  private function testNormalCannotViewTextblockIP(): void {
    $this->login('normal', '1234');
    $this->visitTextblockHistoryPage('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 5, 'N/A');
  }

  private function testNormalCannotViewAttachmentIP(): void {
    $this->visitAttachmentList('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 7, 'N/A');
  }

  private function testNormalCannotViewJobIP(): void {
    $this->visitJobPage(1);
    $this->assertNoText('42.42.42.42');
  }

  private function testHelperCanViewTextblockIP(): void {
    $this->login('helper', '1234');
    $this->visitTextblockHistoryPage('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 5, '42.42.42.42');
  }

  private function testHelperCanViewAttachmentIP(): void {
    $this->visitAttachmentList('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 7, '42.42.42.42');
  }

  private function testHelperCanViewJobIP(): void {
    $this->visitJobPage(1);
    $this->assertTableCellText('table.job', 1, 4, '42.42.42.42');
  }

}
