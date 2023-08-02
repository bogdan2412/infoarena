<?php

class TestTextblockAttachAndDelete extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotAttach();
    $this->testNormalCannotAttach();
    $this->testHelperCannotAttachToProtected();
    $this->attachAsAdmin();

    $this->testAnonCannotDelete();
    $this->testHelperCannotDelete();
    $this->deleteAsAdmin();
  }

  private function testAnonCannotAttach(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockAttachPage('page-public');
    $this->assertLoginRequired();
  }

  private function testNormalCannotAttach(): void {
    $this->login('normal', '1234');
    $this->visitTextblockAttachPage('page-public');
    $this->assertPermissionError();
  }

  private function testHelperCannotAttachToProtected(): void {
    $this->login('helper', '1234');
    $this->visitTextblockAttachPage('page-protected');
    $this->assertPermissionError();
  }

  private function attachAsAdmin(): void {
    $path = realpath(__DIR__ . '/../attachments/file2.txt');
    $this->login('admin', '1234');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Atașează');
    $this->setFileInput('#form_files', $path);
    $this->clickButton('Atașează');
    $this->assertTextExists('Am încărcat un fișier.');
  }

  private function testAnonCannotDelete(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockAttachListPage('page-public');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad(url_login());
    $this->assertLoginRequired();
  }

  private function testHelperCannotDelete(): void {
    $this->login('helper', '1234');
    $this->visitTextblockAttachListPage('page-public');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertPermissionError();
  }

  private function deleteAsAdmin(): void {
    $this->login('admin', '1234');
    $this->visitTextblockAttachListPage('page-public');
    $this->assertTableCellText('table.alternating-colors', 1, 3, 'file2.txt');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();

    $url = Config::URL_HOST . url_textblock('page-public');
    $this->waitForPageLoad($url);

    $this->assertTextExists('Fișierul file2.txt a fost șters cu succes.');
  }

}
