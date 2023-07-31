<?php

class TestUserEditOther extends FunctionalTest {

  function run(): void {
    $this->testHelperCannotEdit();
    $this->testAdminCanEdit();
  }

  private function testHelperCannotEdit(): void {
    $this->login('helper', '1234');
    $this->visitUserAccount('normal');
    $this->assertPermissionError();
  }

  private function testAdminCanEdit(): void {
    $this->login('admin', '1234');

    $this->changeData();
    $this->verifyChangedData();
    $this->restoreData();
  }

  private function changeData(): void {
    $this->visitUserAccount('normal');

    $this->changeInput('#form_passwordold', '1234');
    $this->changeInput('#form_password', '12345');
    $this->changeInput('#form_password2', '12345');
    $this->changeInput('#form_name', 'Normal2 Normal2');
    $this->changeInput('#form_email', 'normal2@example.com');
    $this->changeSelect('#form_security_level', 'Intern');
    $this->clickButton('Salvează');
  }

  private function verifyChangedData(): void {
    $this->visitUserAccount('normal');
    $this->assertInputValue('#form_name', 'Normal2 Normal2');
    $this->assertInputValue('#form_email', 'normal2@example.com');
    $this->assertSelectVisibleText('#form_security_level', 'Intern');
  }

  private function restoreData(): void {
    $this->visitUserAccount('normal');

    $this->changeInput('#form_passwordold', '12345');
    $this->changeInput('#form_password', '1234');
    $this->changeInput('#form_password2', '1234');
    $this->changeInput('#form_name', 'Normal Normal');
    $this->changeInput('#form_email', 'normal@example.com');
    $this->changeSelect('#form_security_level', 'Utilizator normal');
    $this->clickButton('Salvează');
  }

}