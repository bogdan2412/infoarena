<?php

class TestJobViewSourceForced extends FunctionalTest {

  const JOB_ID = 2;
  const JOB_MESSAGE = 'This is source s2.cpp';
  const TASK_ID = 'task2';
  const ROUND_ID = 'round-classic';

  function run(): void {
    $this->testAnonLoginRequired();
    $this->testNormalPermissionError();
    $this->testAdminCanView();

    // Making the task public shouldn't suffice. The task is also part of an
    // upcoming round.
    $this->makeTaskPublic();
    $this->testAnonLoginRequired();
    $this->testNormalPermissionError();
    $this->testAdminCanView();

    // Now the force view sermon and button should become visible.
    $this->removeTaskFromRound();
    $this->testAnonLoginRequired();
    $this->testNormalForceView();
    $this->testAdminCanView();

    // Restore
    $this->makeTaskPrivate();
    $this->addTaskToRound();
  }

  private function testAnonLoginRequired(): void {
    $this->ensureLoggedOut();
    $this->visitJobSourcePage(self::JOB_ID);
    $this->assertLoginRequired();
  }

  private function testNormalPermissionError(): void {
    $this->login('normal', '1234');
    $this->visitJobSourcePage(self::JOB_ID);
    $this->assertPermissionError();
  }

  private function testAdminCanView(): void {
    $this->login('admin', '1234');
    $this->visitJobSourcePage(self::JOB_ID);
    $this->assertSourceVisible();
  }

  private function testNormalForceView(): void {
    $this->login('normal', '1234');
    $this->visitJobSourcePage(self::JOB_ID);
    $this->assertNoText(self::JOB_MESSAGE);
    $this->assertTextExists('This is the fource-view-source-page template.');
    $this->clickButton('Vezi sursa');
    $this->assertSourceVisible();
    $this->assertNoText('This is the fource-view-source-page template.');
  }

  private function assertSourceVisible(): void {
    $this->assertTextExists(self::JOB_MESSAGE);
  }

  private function makeTaskPublic(): void {
    $this->changeTaskSecurity('Public');
  }

  private function makeTaskPrivate(): void {
    $this->changeTaskSecurity('Private');
  }

  private function changeTaskSecurity(string $optionText): void {
    $this->login('admin', '1234');
    $this->visitTaskEditPage(self::TASK_ID);
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_security', $optionText);
    $this->clickButton('Salvează');
  }

  private function removeTaskFromRound(): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage(self::ROUND_ID);
    $this->clickLinkByText('Parametri');
    $sel = $this->getSelectByCss('#_dlb2_form_tasks');
    $sel->selectByValue(self::TASK_ID);
    $this->clickButton('<');
    $this->clickButton('Salvează');
  }

  private function addTaskToRound(): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage(self::ROUND_ID);
    $this->clickLinkByText('Parametri');
    $sel = $this->getSelectByCss('#_dlb1_form_tasks');
    $sel->selectByValue(self::TASK_ID);
    $this->clickButton('>');
    $this->clickButton('Salvează');
  }

}
