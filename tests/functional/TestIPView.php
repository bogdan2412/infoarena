<?php

class TestIPView extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotViewTextblockIP();
    $this->testAnonCannotViewAttachmentIP();
    $this->testAnonCannotViewJobIP();

    $this->testNormalCannotViewTextblockIP();
    $this->testNormalCannotViewAttachmentIP();
    $this->testNormalCannotViewJobIP();

    $this->testInternCanViewTextblockIP();
    $this->testInternCanViewAttachmentIP();
    $this->testInternCanViewJobIP();
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

  private function testInternCanViewTextblockIP(): void {
    $this->login('intern', '1234');
    $this->visitTextblockHistoryPage('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 5, '42.42.42.42');
  }

  private function testInternCanViewAttachmentIP(): void {
    $this->visitAttachmentList('page-protected');
    $this->assertTableCellText('table.alternating-colors', 1, 7, '42.42.42.42');
  }

  private function testInternCanViewJobIP(): void {
    $this->visitJobPage(1);
    $this->assertTableCellText('table.job', 4, 4, '42.42.42.42');
  }

}
