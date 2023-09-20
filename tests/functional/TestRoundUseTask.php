<?php

class TestRoundUseTask extends FunctionalTest {

  function run(): void {
    $this->createRound();
    $this->testInternCannotUsePrivateTask();
    $this->testAdminCanUsePrivateTask();
    $this->deleteRound();
  }

  private function createRound(): void {
    $this->login('intern', '1234');
    $this->clickLinkByText('Concursuri virtuale');
    $this->clickLinkByText('Creează concurs nou');
    $this->changeInput('#form_id', 'new-round');
    $this->clickButton('Creează runda');
    $this->assertTextExists('Am creat runda, acum poți să o editezi.');
    $this->clickLinkByText('Parametri');
  }

  private function testInternCannotUsePrivateTask(): void {
    $this->getElementByCss('#_dlb1_form_tasks option[value="task1"]');
    $this->assertNoElement('#_dlb1_form_tasks option[value="task2"]');
  }

  private function testAdminCanUsePrivateTask(): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage('new-round');
    $this->clickLinkByText('Parametri');

    $this->getElementByCss('#_dlb1_form_tasks option[value="task1"]');
    $this->getElementByCss('#_dlb1_form_tasks option[value="task2"]');
  }

  private function deleteRound(): void {
    $this->clickButton('Șterge runda');

    $this->deleteRelatedTextblocks();

    $this->clickButton('Șterge runda, Forever...');
    $this->acceptConfirmationPopup();
    $this->waitForPageLoad($this->homepageUrl);
    $this->assertTextExists('Am șters runda.');
  }

  private function deleteRelatedTextblocks(): void {
    $checkboxes = $this->getCheckboxesByCss('input[name="textblocks[]"]');
    $checkboxes->selectByIndex(0);
    $checkboxes->selectByIndex(1);

    $this->clickButton('Șterge paginile');
    $this->acceptConfirmationPopup();
    $this->waitForPageTitle('Ștergere textblockuri corelate cu new-round');
  }
}
