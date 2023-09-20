<?php

class TestRoundCreateAndDelete extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotCreate();
    $this->testNormalCanOnlyCreateUserRound();
    $this->createAsNormal();
    $this->testNormalCannotDelete();
    $this->testInternCannotDelete();
    $this->deleteAsAdmin();
  }

  private function testAnonCannotCreate(): void {
    $this->ensureLoggedOut();
    $this->clickLinkByText('Concursuri virtuale');
    $this->clickLinkByText('Creează concurs nou');
    $this->assertLoginRequired();
  }

  private function testNormalCanOnlyCreateUserRound(): void {
    $this->login('normal', '1234');
    $this->clickLinkByText('Concursuri virtuale');
    $this->clickLinkByText('Creează concurs nou');
    $this->assertSelectNumOptions('#form_type', 1);
    $this->assertSelectVisibleText('#form_type', 'Concurs virtual');
  }

  private function createAsNormal(): void {
    $this->changeInput('#form_id', 'new-round');
    $this->clickButton('Creează runda');
    $this->assertTextExists('Am creat runda, acum poți să o editezi.');
  }

  private function testNormalCannotDelete(): void {
    $this->clickLinkByText('Parametri');
    $this->clickButton('Șterge runda');
    $this->assertPermissionError();
  }

  private function testInternCannotDelete(): void {
    $this->login('intern', '1234');
    $this->visitRoundEditPage('new-round');
    $this->clickLinkByText('Parametri');
    $this->clickButton('Șterge runda');
    $this->assertPermissionError();
  }

  private function deleteAsAdmin(): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage('new-round');
    $this->clickLinkByText('Parametri');
    $this->clickButton('Șterge runda');

    $this->deleteRelatedTextblocks();

    $this->clickButton('Șterge runda, Forever...');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertTextExists('Am șters runda.');
  }

  private function deleteRelatedTextblocks(): void {
    $this->assertTextExists('Paginile corelate');

    $checkboxes = $this->getCheckboxesByCss('input[name="textblocks[]"]');
    $numCheckboxes = count($checkboxes->getOptions());
    $this->assert($numCheckboxes == 2,
                  "Expected 2 checkboxes, found {$numCheckboxes}.");
    $checkboxes->selectByIndex(0);
    $checkboxes->selectByIndex(1);

    $this->clickButton('Șterge paginile');
    $this->acceptConfirmationPopup();
    $this->waitForElementByCss('div.flash.flash-success');
    $this->assertTextExists('Am șters 2 textblocks.');
  }

}
