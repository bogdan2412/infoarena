<?php

class TestTextblockEdit extends FunctionalTest {

  private array $origPage;

  function run(): void {
    $this->testAnonCannotEdit();
    $this->testNormalCannotEdit();
    $this->testHelperCannotEditProtected();
    $this->testAdminCanEditPrivate();
  }

  private function testAnonCannotEdit(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockEditPage('page-public');
    $this->assertLoginRequired();
  }

  private function testNormalCannotEdit(): void {
    $this->login('normal', '1234');
    $this->visitTextblockEditPage('page-public');
    $this->assertPermissionError();
  }

  private function testHelperCannotEditProtected(): void {
    $this->login('helper', '1234');
    $this->visitTextblockEditPage('page-protected');
    $this->assertPermissionError();
  }

  private function testAdminCanEditPrivate(): void {
    $this->origPage = textblock_get_revision('page-private');
    $this->login('admin', '1234');

    $this->changePage();
    $this->verifyPage();
    $this->restorePage();
  }

  private function changePage(): void {
    $this->visitTextblockPage('page-private');
    $this->clickLinkByText('Editează');

    $this->changeInput('#form_title', 'abc');
    $this->changeInput('#form_text', 'def');
    $this->clickButton('Salvează');
  }

  private function verifyPage(): void {
    $this->visitTextblockEditPage('page-private');
    $this->assertInputValue('#form_title', 'abc');
    $this->assertInputValue('#form_text', 'def');
  }

  private function restorePage(): void {
    $this->visitTextblockEditPage('page-private');
    $this->changeInput('#form_title', $this->origPage['title']);
    $this->changeInput('#form_text', $this->origPage['text']);
    $this->clickButton('Salvează');
  }

}
