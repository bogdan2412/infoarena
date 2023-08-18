<?php

class TestRoundEdit extends FunctionalTest {

  private Round $origRound;
  private array $origParams;
  private array $origPage;

  function run(): void {
    $this->testAnonCannotEdit();
    $this->testNormalCannotEditClassic();
    $this->testNormalCanEditUserDefined();
    $this->editAsAdmin();
  }

  private function testAnonCannotEdit(): void {
    $this->ensureLoggedOut();
    $this->visitRoundEditPage('round-classic');
    $this->assertLoginRequired();
  }

  private function testNormalCannotEditClassic(): void {
    $this->login('normal', '1234');
    $this->visitRoundEditPage('round-classic');
    $this->assertPermissionError();
  }

  private function testNormalCanEditUserDefined(): void {
    $this->login('normal', '1234');
    $this->visitRoundEditPage('round-user');
    $this->assertOnRoundEditPage('round-user');
  }

  private function editAsAdmin(): void {
    $this->login('admin', '1234');
    $this->origRound = Round::get_by_id('round-classic');
    $this->origParams = round_get_parameters('round-classic');
    $this->origPage = textblock_get_revision('runda/round-classic');

    $this->editStatement();
    $this->verifyStatement();
    $this->restoreStatement();

    $this->editParams();
    $this->verifyParams();
    $this->restoreParams();
  }

  private function editStatement(): void {
    $this->visitRoundEditPage('round-classic');
    $this->changeInput('#form_title', 'abc');
    $this->changeInput('#form_text', 'def');
    // Don't change it: weird things happen whatever other option we choose.
    $this->changeSelect('#security_select', 'Round');
    $this->clickButton('Salvează');
  }

  private function verifyStatement(): void {
    $this->visitRoundEditPage('round-classic');
    $this->assertInputValue('#form_title', 'abc');
    $this->assertInputValue('#form_text', 'def');
    $this->assertSelectVisibleText('#security_select', 'Round');
  }

  private function restoreStatement(): void {
    sleep(1);
    $this->changeInput('#form_title', $this->origPage['title']);
    $this->changeInput('#form_text', $this->origPage['text']);
    $this->clickButton('Salvează');
  }

  private function editParams(): void {
    $this->visitRoundEditPage('round-classic');
    $this->clickLinkByText('Parametri');
    $this->changeInput('#form_title', 'abc');
    $this->changeInput('#form_page_name', 'runda/abc');
    $this->changeInput('#form_start_time', '2023-01-31 12:34:56');

    // remove tasks
    $sel = $this->getSelectByCss('#_dlb2_form_tasks');
    $sel->selectByValue('task1');
    $sel->selectByValue('task2');
    $this->clickButton('<');

    $this->changeSelect('#form_public_eval', 'Nu');
    $this->changeInput('#form_param_classic_duration', '8');
    $this->changeSelect('#form_param_classic_rating_update', 'Nu');
    $this->clickButton('Salvează');
    $this->assertTextExists('Am modificat runda.');
  }

  private function verifyParams(): void {
    // Stay on the same page! The page name is wrong so the round is inaccessible.
    $this->assertInputValue('#form_title', 'abc');
    $this->assertInputValue('#form_page_name', 'runda/abc');
    $this->assertInputValue('#form_start_time', '2023-01-31 12:34:56');
    $this->assertSelectNumOptions('#_dlb1_form_tasks', 3);
    $this->assertSelectNumOptions('#_dlb2_form_tasks', 0);
    $this->assertSelectVisibleText('#form_public_eval', 'Nu');
    $this->assertInputValue('#form_param_classic_duration', '8');
    $this->assertSelectVisibleText('#form_param_classic_rating_update', 'Nu');
  }

  private function restoreParams(): void {
    $this->changeInput('#form_title', $this->origRound->title);
    $this->changeInput('#form_page_name', $this->origRound->page_name);
    $this->changeInput('#form_start_time', $this->origRound->start_time);

    // re-add tasks
    $sel = $this->getSelectByCss('#_dlb1_form_tasks');
    $sel->selectByValue('task1');
    $sel->selectByValue('task2');
    $this->clickButton('>');

    $this->changeSelect('#form_public_eval', 'Da');
    $this->changeInput('#form_param_classic_duration', $this->origParams['duration']);
    $this->changeSelect('#form_param_classic_rating_update', 'Da');
    $this->clickButton('Salvează');
    $this->assertTextExists('Am modificat runda.');
  }

}
