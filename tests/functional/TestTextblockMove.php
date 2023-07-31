<?php

class TestTextblockMove extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotMove();
    $this->testHelperCannotMove();
    $this->moveAsAdmin();
  }

  private function testAnonCannotMove(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockMovePage('page-public');
    $this->assertLoginRequired();
  }

  private function testHelperCannotMove(): void {
    $this->login('helper', '1234');
    $this->visitTextblockMovePage('page-public');
    $this->assertPermissionError();
  }

  private function moveAsAdmin(): void {
    $this->login('admin', '1234');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Mută');
    $this->changeInput('#form_new_name', 'some-other-name');
    $this->clickButton('Mută pagina');
    $this->assertTextExists('Am mutat pagina.');

    // put it back
    $this->clickLinkByText('Mută');
    $this->changeInput('#form_new_name', 'page-public');
    $this->clickButton('Mută pagina');
    $this->assertTextExists('Am mutat pagina.');
  }
}
