<?php

class TestTextblockHistoryView extends FunctionalTest {

  function run(): void {
    $this->testHelperCannotViewPrivate();
    $this->testAdminView();
  }

  private function testHelperCannotViewPrivate(): void {
    $this->login('helper', '1234');
    $this->visitTextblockHistoryPage('page-private');
    $this->assertPermissionError();
  }

  private function testAdminView(): void {
    $this->login('admin', '1234');
    $this->visitTextblockPage('page-private');
    $this->clickLinkByText('Istoria');
    $this->assertTextExists('Istoria paginii');
  }

}
