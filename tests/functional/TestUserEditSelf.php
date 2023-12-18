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
    $this->changeInput('#form_name', 'NormalX NormalX');
    $this->changeInput('#form_email', 'normalX@nerdarena.ro');
    $this->clickButton('Salvează');
  }

  private function verifyChangedData(): void {
    $nameElem = $this->getElementByCss('#userbox .user strong');
    $this->assert($nameElem->getText() == 'NormalX NormalX',
                  'Expected name NormalX NormalX.');

    $this->visitOwnAccount();
    $this->assertInputValue('#form_email', 'normalX@nerdarena.ro');
  }

  private function restoreData(): void {
    $this->visitOwnAccount();

    $this->changeInput('#form_passwordold', '12345');
    $this->changeInput('#form_password', '1234');
    $this->changeInput('#form_password2', '1234');
    $this->changeInput('#form_name', 'Normal Normal');
    $this->changeInput('#form_email', 'normal@nerdarena.ro');
    $this->clickButton('Salvează');
  }

}
