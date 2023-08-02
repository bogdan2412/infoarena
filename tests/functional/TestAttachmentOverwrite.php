<?php

class TestAttachmentOverwrite extends FunctionalTest {

  function run(): void {
    $this->testHelperCanAttachNewFile();
    $this->testHelperCannotOverwrite();
    $this->deleteAddedAttachment();
    $this->testAdminCanOverwrite();
  }

  private function testHelperCanAttachNewFile(): void {
    $path = $this->getUploadFullPath('file2.txt');
    $this->login('helper', '1234');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Atașează');
    $this->setFileInput('#form_files', $path);
    $this->clickButton('Atașează');
    $this->assertTextExists('Am încărcat un fișier.');
  }

  private function testHelperCannotOverwrite(): void {
    $path = $this->getUploadFullPath('file1.txt');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Atașează');
    $this->setFileInput('#form_files', $path);
    $this->clickButton('Atașează');
    $this->assertPermissionError();
  }

  private function deleteAddedAttachment(): void {
    $this->login('admin', '1234');
    $this->visitAttachmentList('page-public');
    $this->assertTableCellText('table.alternating-colors', 1, 3, 'file2.txt');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();

    $url = Config::URL_HOST . url_textblock('page-public');
    $this->waitForPageLoad($url);

    $this->assertTextExists('Fișierul file2.txt a fost șters cu succes.');
  }
  private function testAdminCanOverwrite(): void {
    $path = $this->getUploadFullPath('file1.txt');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Atașează');
    $this->setFileInput('#form_files', $path);
    $this->clickButton('Atașează');
    $this->assertTextExists('Am încărcat un fișier. Un fișier mai vechi a fost rescris.');
  }

}
