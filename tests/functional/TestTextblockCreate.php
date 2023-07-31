<?php

class TestTextblockCreate extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotCreate();
    $this->testNormalCannotCreate();
    $this->createAsHelper();
    $this->deleteAsAdmin();
  }

  private function testAnonCannotCreate(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockPage('stuff');
    $this->assertLoginRequired();
  }

  private function testNormalCannotCreate(): void {
    $this->login('normal', '1234');
    $this->visitTextblockPage('stuff');
    $this->assertPermissionError();
  }

  private function createAsHelper(): void {
    $this->login('helper', '1234');
    $this->visitTextblockPage('stuff');
    $this->changeInput('#form_title', 'stuff title');
    $this->changeInput('#form_text', 'stuff body');
    $this->clickButton('Salvează');
    $this->assertTextExists('Am actualizat conținutul.');
    $this->visitTextblockEditPage('stuff');
    $this->assertInputValue('#form_title', 'stuff title');
    $this->assertInputValue('#form_text', 'stuff body');
  }

  private function deleteAsAdmin(): void {
    $this->login('admin', '1234');
    $this->visitTextblockPage('stuff');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertTextExists('Am șters pagina.');
  }

}
