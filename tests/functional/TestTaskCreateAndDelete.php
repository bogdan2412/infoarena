<?php

class TestTaskCreateAndDelete extends FunctionalTest {

  private string $createUrl;

  function run(): void {
    $this->createUrl = Config::URL_HOST . url_task_create();
    $this->testAnonCannotCreate();
    $this->testNormalCannotCreate();
    $this->createAsHelper();
    $this->deleteAsIntern();
  }

  private function testAnonCannotCreate(): void {
    $this->ensureLoggedOut();
    $this->driver->get($this->createUrl);
    $this->assertLoginRequired();
  }

  private function testNormalCannotCreate(): void {
    $this->login('normal', '1234');
    $this->driver->get($this->createUrl);
    $this->assertPermissionError();
  }

  private function createAsHelper(): void {
    $this->login('helper', '1234');
    $this->driver->get($this->createUrl);
    $this->changeInput('#form_id', 'new-task');
    $this->clickButton('Creează task');
    $this->assertTextExists('Am creat problema, acum poți să o editezi.');
  }

  private function deleteAsIntern(): void {
    $this->login('intern', '1234');
    $this->visitTaskPage('new-task');
    $this->clickLinkByText('Editează');
    $this->clickLinkByText('Parametri');
    $this->clickButton('Șterge problema');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertTextExists('Am șters problema.');
  }

}
