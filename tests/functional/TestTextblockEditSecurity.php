<?php

class TestTextblockEditSecurity extends FunctionalTest {

  function run(): void {
    $this->testHelperCannotEdit();
    $this->testAdminCanEdit();
  }

  private function testHelperCannotEdit(): void {
    $this->login('helper', '1234');
    $this->visitTextblockEditPage('page-public');
    $this->assertInputValue('#form_title', 'page-public');
    $this->assertNoElement('#security_select');
  }

  private function testAdminCanEdit(): void {
    $this->login('admin', '1234');

    $this->changePage();
    $this->verifyPage();
    $this->restorePage();
  }

  private function changePage(): void {
    $this->visitTextblockEditPage('page-public');
    $this->assertSelectVisibleText('#security_select', 'Public');
    $this->changeSelect('#security_select', 'Protected');
    $this->clickButton('Salvează');
  }

  private function verifyPage(): void {
    $this->visitTextblockEditPage('page-public');
    $this->assertSelectVisibleText('#security_select', 'Protected');
  }

  private function restorePage(): void {
    sleep(1);
    $this->visitTextblockEditPage('page-public');
    $this->changeSelect('#security_select', 'Public');
    $this->clickButton('Salvează');
  }

}
