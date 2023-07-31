<?php

class TestTaskEdit extends FunctionalTest {

  private Task $origTask;
  private array $origParams;
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
    $this->origParams = task_get_parameters('task2');
    $this->origPage = textblock_get_revision('problema/task2');

    $this->editStatement();
    $this->verifyStatement();
    $this->restoreStatement();

    $this->editParams();
    $this->verifyParams();
    $this->restoreParams();
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

  private function editParams(): void {
    $this->visitTaskEditPage('task2');
    $this->clickLinkByText('Parametri');
    $this->changeInput('#form_title', 'abc');
    $this->changeInput('#form_user', 'intern');
    $this->changeInput('#form_source', 'def');
    $this->changeSelect('#form_security', 'Public');
    $this->changeInput('#form_tag_author', 'ghi');
    $this->changeInput('#form_tag_contest', 'jkl');
    $this->changeInput('#form_tag_year', '2000');
    $this->changeInput('#form_tag_round', 'mno');
    $this->changeInput('#form_tag_age_group', 'pqr');
    $this->changeSelect('#form_open_source', 'Da');
    $this->changeSelect('#form_open_tests', 'Da');
    $this->changeInput('#form_test_count', '10');
    $this->changeInput('#form_test_groups', '1;2;3;4;5;6;7;8;9;10');
    $this->changeInput('#form_public_tests', '1,2,3,4');
    $this->changeSelect('#form_use_ok_files', 'Nu');
    $this->changeInput('#form_evaluator', 'diff.c');
    $this->changeInput('#form_param_classic_timelimit', '42');
    $this->changeInput('#form_param_classic_memlimit', '1024');
    $this->clickButton('Salvează');
  }

  private function verifyParams(): void {
    $this->visitTaskEditPage('task2');
    $this->clickLinkByText('Parametri');
    $this->assertInputValue('#form_title', 'abc');
    $this->assertInputValue('#form_user', 'intern');
    $this->assertInputValue('#form_source', 'def');
    $this->assertSelectVisibleText('#form_security', 'Public');
    $this->assertInputValue('#form_tag_author', 'ghi');
    $this->assertInputValue('#form_tag_contest', 'jkl');
    $this->assertInputValue('#form_tag_year', '2000');
    $this->assertInputValue('#form_tag_round', 'mno');
    $this->assertInputValue('#form_tag_age_group', 'pqr');
    $this->assertSelectVisibleText('#form_open_source', 'Da');
    $this->assertSelectVisibleText('#form_open_tests', 'Da');
    $this->assertInputValue('#form_test_count', '10');
    $this->assertInputValue('#form_test_groups', '1;2;3;4;5;6;7;8;9;10');
    $this->assertInputValue('#form_public_tests', '1,2,3,4');
    $this->assertSelectVisibleText('#form_use_ok_files', 'Nu');
    $this->assertInputValue('#form_evaluator', 'diff.c');
    $this->assertInputValue('#form_param_classic_timelimit', '42');
    $this->assertInputValue('#form_param_classic_memlimit', '1024');
  }

  private function restoreParams(): void {
    $origUser = User::get_by_id($this->origTask->user_id);

    $this->visitTaskEditPage('task2');
    $this->clickLinkByText('Parametri');
    $this->changeInput('#form_title', $this->origTask->title);
    $this->changeInput('#form_user', $origUser->username);
    $this->changeInput('#form_source', $this->origTask->source);
    $this->changeSelect('#form_security', ucfirst($this->origTask->security));
    $this->changeInput('#form_tag_author', '');
    $this->changeInput('#form_tag_contest', '');
    $this->changeInput('#form_tag_year', '');
    $this->changeInput('#form_tag_round', '');
    $this->changeInput('#form_tag_age_group', '');
    $this->changeSelect('#form_open_source', 'Nu');
    $this->changeSelect('#form_open_tests', 'Nu');
    $this->changeInput('#form_test_count', $this->origTask->test_count);
    $this->changeInput('#form_test_groups', $this->origTask->test_groups);
    $this->changeInput('#form_public_tests', $this->origTask->public_tests);
    $this->changeSelect('#form_use_ok_files', 'Da');
    $this->changeInput('#form_evaluator', $this->origTask->evaluator);
    $this->changeInput('#form_param_classic_timelimit', $this->origParams['timelimit']);
    $this->changeInput('#form_param_classic_memlimit', $this->origParams['memlimit']);
    $this->clickButton('Salvează');
  }
}
