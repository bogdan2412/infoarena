<?php

class TestTextblockDeleteRevision extends FunctionalTest {

  function run(): void {
    $this->testHelperCannotDelete();
    $this->deleteAsAdmin();
  }

  private function testHelperCannotDelete(): void {
    $this->login('helper', '1234');
    $this->visitTextblockHistoryPage('page-public');
    $this->clickLinkByText('Șterge');
    $this->assertPermissionError();
  }

  private function deleteAsAdmin(): void {
    $this->login('admin', '1234');
    $this->changePage();
    $this->deleteVersion();
  }

  private function changePage(): void {
    $this->visitTextblockEditPage('page-public');
    $this->changeInput('#form_text', 'abcde');
    $this->clickButton('Salvează');
    $this->assertTextExists('abcde');
  }

  private function deleteVersion(): void {
    $this->clickLinkByText('Istoria');
    $this->clickLinkByText('Șterge');
    $this->assertTextExists('Am șters revizia.');
    $this->clickLinkByText('page-public');
    $this->assertTextExists('This is revision 5 of page-public.');
    $this->assertNoText('abcde');
  }

}
