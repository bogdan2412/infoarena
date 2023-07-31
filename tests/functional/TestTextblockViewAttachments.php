<?php

class TestTextblockViewAttachments extends FunctionalTest {

  function run(): void {
    $this->testNormalView();
    $this->testAdminView();
  }

  private function testNormalView(): void {
    $this->login('normal', '1234');

    $this->visitAttachmentList('page-public');
    $this->assertTextExists('Atașamente pentru pagina');

    $this->visitAttachmentList('page-protected');
    $this->assertTextExists('Atașamente pentru pagina');

    $this->visitAttachmentList('page-private');
    $this->assertPermissionError();
  }

  private function testAdminView(): void {
    $this->login('admin', '1234');

    $this->visitAttachmentList('page-public');
    $this->assertTextExists('Atașamente pentru pagina');

    $this->visitAttachmentList('page-protected');
    $this->assertTextExists('Atașamente pentru pagina');

    $this->visitAttachmentList('page-private');
    $this->assertTextExists('Atașamente pentru pagina');
  }

}
