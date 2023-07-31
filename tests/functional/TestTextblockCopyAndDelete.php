<?php

class TestTextblockCopyAndDelete extends FunctionalTest {

  function run(): void {
    $this->makeCopyAsHelper();
    $this->testHelperCannotDeleteCopy();
    $this->deleteCopyAsAdmin();
  }

  private function makeCopyAsHelper(): void {
    $this->login('helper', '1234');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Copiază');
    $this->assertTextExists('Copiază page-public');
    $this->changeInput('#form_new_name', 'page-public-copy');
    $this->clickButton('Copiază pagina');
    $this->assertOnTextblockPage('page-public-copy');
  }

  private function testHelperCannotDeleteCopy(): void {
    $this->getLinkByText('Editează');
    $this->getLinkByText('Copiază');
    $this->assertNoLink('Șterge');
  }

  private function deleteCopyAsAdmin(): void {
    $this->login('admin', '1234');
    $this->visitTextblockPage('page-public-copy');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertTextExists('Am șters pagina.');
  }

}
