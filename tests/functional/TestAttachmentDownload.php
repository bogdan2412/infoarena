<?php

class TestAttachmentDownload extends FunctionalTest {

  function run(): void {
    $this->testAnonCanViewPublic();
    $this->testAnonCanViewProtected();
    $this->testAnonCannotViewPrivate();
    $this->testInternCannotViewPrivate();
    $this->testAdminCanViewPrivate();
  }

  private function testAnonCanViewPublic(): void {
    $this->ensureLoggedOut();
    $this->visitAttachmentList('page-public');
    $this->assertLinkDownloadsContent('file1.txt', 'file1.txt');
  }

  private function testAnonCanViewProtected(): void {
    $this->ensureLoggedOut();
    $this->visitAttachmentList('page-protected');
    $this->assertLinkDownloadsContent('file1.txt', 'file1.txt');
  }

  private function testAnonCannotViewPrivate(): void {
    $this->ensureLoggedOut();
    $this->visitAttachmentList('page-private');
    $this->assertLoginRequired();
  }

  private function testInternCannotViewPrivate(): void {
    $this->login('intern', '1234');
    $this->visitAttachmentList('page-private');
    $this->assertPermissionError();
  }

  private function testAdminCanViewPrivate(): void {
    $this->login('admin', '1234');
    $this->visitAttachmentList('page-private');
    $this->assertLinkDownloadsContent('file1.txt', 'file1.txt');
  }

}
