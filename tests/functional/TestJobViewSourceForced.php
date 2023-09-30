<?php

class TestJobViewSourceForced extends FunctionalTest {

  const JOB_ID = 6;
  const JOB_MESSAGE = 'This is source s3.c';
  const TASK_ID = 'task1';
  const ROUND_ID = 'round-classic';

  function run(): void {
    $this->testAnonLoginRequired();
    $this->testNormalPermissionError();
    $this->testUserCanView('admin');

    // Making the task public shouldn't suffice. The task is also part of some
    // upcoming rounds.
    $this->makeTaskClosedSource();
    $this->testAnonLoginRequired();
    $this->testNormalPermissionError();
    $this->testUserCanView('admin');

    // Now the source should be visible to people who scored 100.
    $this->removeTaskFromRounds();
    $this->testNormalPermissionError();
    $this->testUserCanView('normal2'); // because normal2 has 100p

    // Restore
    $this->makeTaskOpenSource();
    $this->addTaskToRounds();
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

  private function testUserCanView($username): void {
    $this->login($username, '1234');
    $this->visitJobSourcePage(self::JOB_ID);
    $this->assertSourceVisible();
  }

  private function assertSourceVisible(): void {
    $this->assertTextExists(self::JOB_MESSAGE);
  }

  private function makeTaskClosedSource(): void {
    $this->changeTaskParams('Nu');
  }

  private function makeTaskOpenSource(): void {
    $this->changeTaskParams('Da');
  }

  private function changeTaskParams(string $openText): void {
    $this->login('admin', '1234');
    $this->visitTaskEditPage(self::TASK_ID);
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_open_source', $openText);
    $this->clickButton('Salvează');
  }

  private function removeTaskFromRounds(): void {
    $this->removeTaskFromRound('round-classic');
    $this->removeTaskFromRound('round-penalty');
    $this->removeTaskFromRound('round-user');
  }

  private function removeTaskFromRound($roundId): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage($roundId);
    $this->clickLinkByText('Parametri');
    $sel = $this->getSelectByCss('#_dlb2_form_tasks');
    $sel->selectByValue(self::TASK_ID);
    $this->clickButton('<');
    $this->clickButton('Salvează');
  }

  private function addTaskToRounds(): void {
    $this->addTaskToRound('round-classic');
    $this->addTaskToRound('round-penalty');
    $this->addTaskToRound('round-user');
  }

  private function addTaskToRound($roundId): void {
    $this->login('admin', '1234');
    $this->visitRoundEditPage($roundId);
    $this->clickLinkByText('Parametri');
    $sel = $this->getSelectByCss('#_dlb1_form_tasks');
    $sel->selectByValue(self::TASK_ID);
    $this->clickButton('>');
    $this->clickButton('Salvează');
  }

}
