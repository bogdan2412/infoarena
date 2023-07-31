<?php

class TestTextblockView extends FunctionalTest {

  function run(): void {
    $this->testNormalView();
    $this->testAdminView();
  }

  private function testNormalView(): void {
    $this->login('normal', '1234');

    $this->visitTextblockPage('page-public');
    $this->assertTextExists('Contents of the page-public page.');

    $this->visitTextblockPage('page-protected');
    $this->assertTextExists('Contents of the page-protected page.');

    $this->visitTextblockPage('page-private');
    $this->assertPermissionError();
  }

  private function testAdminView(): void {
    $this->login('admin', '1234');

    $this->visitTextblockPage('page-public');
    $this->assertTextExists('Contents of the page-public page.');

    $this->visitTextblockPage('page-protected');
    $this->assertTextExists('Contents of the page-protected page.');

    $this->visitTextblockPage('page-private');
    $this->assertTextExists('Contents of the page-private page.');
  }

}
