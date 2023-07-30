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
    $sel = $this->getSelectByCss('#form_security_level');
    $sel->selectByVisibleText('Intern');
    $this->getElementByCss('#form_submit')->click();
  }

  private function verifyChangedData(): void {
    $this->visitUserAccount('normal');
    $name = $this->getElementByCss('#form_name')->getAttribute('value');
    $this->assert($name == 'Normal2 Normal2',
                  'Expected name Normal2 Normal2.');
    $email = $this->getElementByCss('#form_email')->getAttribute('value');
    $this->assert($email == 'normal2@example.com',
                  'Expected email normal2@example.com.');

    $sel = $this->getSelectByCss('#form_security_level');
    $text = $sel->getFirstSelectedOption()->getText();
    $this->assert($text == 'Intern',
                  "Expected security level Intern, found {$text}.");
  }

  private function restoreData(): void {
    $this->visitUserAccount('normal');

    $this->changeInput('#form_passwordold', '12345');
    $this->changeInput('#form_password', '1234');
    $this->changeInput('#form_password2', '1234');
    $this->changeInput('#form_name', 'Normal Normal');
    $this->changeInput('#form_email', 'normal@example.com');
    $sel = $this->getSelectByCss('#form_security_level');
    $sel->selectByVisibleText('Utilizator normal');
    $this->getElementByCss('#form_submit')->click();
  }

}
