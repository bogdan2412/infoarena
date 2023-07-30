<?php

class TestUserEditSelf extends FunctionalTest {

  function run(): void {
    $this->login('normal', '1234');

    $this->changeData();
    $this->verifyChangedData();
    $this->restoreData();
  }

  private function changeData(): void {
    $this->visitOwnAccount();

    $this->assertNoElement('#form_security_level');

    $this->changeInput('#form_passwordold', '1234');
    $this->changeInput('#form_password', '12345');
    $this->changeInput('#form_password2', '12345');
    $this->changeInput('#form_name', 'Normal2 Normal2');
    $this->changeInput('#form_email', 'normal2@example.com');
    $this->clickButton('Salvează');
  }

  private function verifyChangedData(): void {
    $nameElem = $this->getElementByCss('#userbox .user strong');
    $this->assert($nameElem->getText() == 'Normal2 Normal2',
                  'Expected name Normal2 Normal2.');

    $this->visitOwnAccount();
    $this->assertInputValue('#form_email', 'normal2@example.com');
  }

  private function restoreData(): void {
    $this->visitOwnAccount();

    $this->changeInput('#form_passwordold', '12345');
    $this->changeInput('#form_password', '1234');
    $this->changeInput('#form_password2', '1234');
    $this->changeInput('#form_name', 'Normal Normal');
    $this->changeInput('#form_email', 'normal@example.com');
    $this->clickButton('Salvează');
  }

}
