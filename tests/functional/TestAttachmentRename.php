<?php

class TestAttachmentRename extends FunctionalTest {

  function run(): void {
    $this->addSecondAttachmentToCircumventIssue53();
    $this->testHelperCannotRename();
    $this->testAdminCannotRenameBadName();
    $this->adminRename();
    $this->adminRestoreOriginalName();
    $this->deleteSecondAttachment();
  }

  private function addSecondAttachmentToCircumventIssue53(): void {
    $path = $this->getUploadFullPath('file2.txt');
    $this->login('helper', '1234');
    $this->visitTextblockPage('page-public');
    $this->clickLinkByText('Atașează');
    $this->setFileInput('#form_files', $path);
    $this->clickButton('Atașează');
    $this->assertTextExists('Am încărcat un fișier.');
  }

  private function testHelperCannotRename(): void {
    $this->visitAttachmentList('page-public');
    $this->getElementByCss('#rename_link_2')->click();
    $inputSel = 'input[type="text"][value="file1.txt"]';
    $input = $this->getElementByCss($inputSel);
    $this->changeInput($inputSel, 'file3.txt');
    // The [OK] button is not unique.
    $input->submit();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertPermissionError();
  }

  private function testAdminCannotRenameBadName(): void {
    $this->login('admin', '1234');
    $this->visitAttachmentList('page-public');
    $this->getElementByCss('#rename_link_2')->click();
    $inputSel = 'input[type="text"][value="file1.txt"]';
    $input = $this->getElementByCss($inputSel);
    $this->changeInput($inputSel, 'file2.txt');
    $input->submit();
    sleep(1);
    $this->assertTextExists('Există deja un fișier cu numele file2.txt atașat paginii page-public.');
  }

  private function adminRename(): void {
    $this->visitAttachmentList('page-public');
    $this->getElementByCss('#rename_link_2')->click();
    $inputSel = 'input[type="text"][value="file1.txt"]';
    $input = $this->getElementByCss($inputSel);
    $this->changeInput($inputSel, 'file3.txt');
    $input->submit();
    sleep(1);
    $this->assertTextExists('Fișierul file1.txt a fost redenumit cu succes în file3.txt');
  }

  private function adminRestoreOriginalName(): void {
    $this->visitAttachmentList('page-public');
    $this->getElementByCss('#rename_link_2')->click();
    $inputSel = 'input[type="text"][value="file3.txt"]';
    $input = $this->getElementByCss($inputSel);
    $this->changeInput($inputSel, 'file1.txt');
    $input->submit();
    sleep(1);
    $this->assertTextExists('Fișierul file3.txt a fost redenumit cu succes în file1.txt');
  }

  private function deleteSecondAttachment(): void {
    $this->visitAttachmentList('page-public');
    $this->clickLinkByText('Șterge');
    $this->acceptConfirmationPopup();

    $url = Config::URL_HOST . url_textblock('page-public');
    $this->waitForPageLoad($url);

    $this->assertTextExists('Fișierul file2.txt a fost șters cu succes.');
  }

}
