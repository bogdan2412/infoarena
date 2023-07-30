<?php

class TestTaskEdit extends FunctionalTest {

  private Task $origTask;
  private array $origPage;

  function run(): void {
    $this->testAnonCannotEdit();
    $this->testNormalCannotEdit();
    $this->testOwnerHelperCanEdit();
    $this->testInternCanEdit();
    $this->testInternCannotEditCritical();
    $this->editAsAdmin();
  }

  private function testAnonCannotEdit(): void {
    $this->ensureLoggedOut();
    $this->visitTaskEditPage('task1');
    $this->assertLoginRequired();
  }

  private function testNormalCannotEdit(): void {
    $this->login('normal', '1234');
    $this->visitTaskEditPage('task1');
    $this->assertPermissionError();
  }

  private function testOwnerHelperCanEdit(): void {
    $this->login('helper', '1234');
    $this->visitTaskEditPage('task2');
    $this->assertOnTaskEditPage('task2');
  }

  private function testInternCanEdit(): void {
    $this->login('intern', '1234');
    $this->visitTaskEditPage('task2');
    $this->assertOnTaskEditPage('task2');
  }

  private function testInternCannotEditCritical(): void {
    $this->login('intern', '1234');
    $this->visitTaskEditPage('task2');
    $this->assertNoElement('#security_select');
    $this->clickLinkByText('Parametri');
    $this->assertNoElement('#form_user');
    $this->assertNoElement('#form_open_source');
    $this->assertNoElement('#form_open_tests');
  }

  private function editAsAdmin(): void {
    $this->login('admin', '1234');
    $this->origTask = Task::get_by_id('task2');
    $this->origPage = textblock_get_revision('problema/task2');

    $this->editStatement();
    $this->verifyStatement();
    $this->restoreStatement();
  }

  private function editStatement(): void {
    $this->visitTaskEditPage('task2');
    $this->changeInput('#form_title', 'abc');
    $this->changeInput('#form_text', 'def');
    // Don't change it: weird things happen whatever other option we choose.
    $this->changeSelect('#security_select', 'Task');
    $this->clickButton('Salvează');
  }

  private function verifyStatement(): void {
    $this->visitTaskEditPage('task2');
    $this->assertInputValue('#form_title', 'abc');
    $this->assertInputValue('#form_text', 'def');
    $this->assertSelectVisibleText('#security_select', 'Task');
  }

  private function restoreStatement(): void {
    $this->changeInput('#form_title', $this->origPage['title']);
    $this->changeInput('#form_text', $this->origPage['text']);
    $this->clickButton('Salvează');
  }
}
