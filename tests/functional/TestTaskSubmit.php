<?php

class TestTaskSubmit extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotSubmit();
    $this->testNormalCannotSubmitToPrivateTask();
    $this->testHelperCanSubmitToOwnTask();
    $this->testHelperSubmitFromTask();
    $this->deleteLastSubmittedJob();
    $this->testHelperSubmitFromSubmitPage();
    $this->deleteLastSubmittedJob();
  }

  private function testAnonCannotSubmit(): void {
    $this->ensureLoggedOut();
    $this->visitSubmitPage();
    $this->assertLoginRequired();
  }

  private function testNormalCannotSubmitToPrivateTask(): void {
    $this->login('normal', '1234');
    $this->visitSubmitPage();
    $this->getElementByCss('#form_task option[value="task1"]');
    $this->assertNoElement('#form_task option[value="task2"]');
  }

  private function testHelperCanSubmitToOwnTask(): void {
    $this->login('helper', '1234');
    $this->visitSubmitPage();
    $this->getElementByCss('#form_task option[value="task1"]');
    $this->getElementByCss('#form_task option[value="task2"]');
  }

  private function testHelperSubmitFromTask(): void {
    $this->login('helper', '1234');
    $this->visitTaskPage('task2');
    $this->fillFormAndSubmit();
    $this->assertOnTaskPage('task2');
  }

  private function testHelperSubmitFromSubmitPage(): void {
    $this->login('helper', '1234');
    $this->visitSubmitPage();
    $this->changeSelect('#form_task', 'Task 2');
    $this->fillFormAndSubmit();
    $this->assertOnSubmitPage();
  }

  private function fillFormAndSubmit(): void {
    $path = $this->getSourceFullPath('s1.cpp');
    $this->setFileInput('#form_solution', $path);
    $this->assertSelectVisibleText('#form_round', 'round-archive');
    $this->assertSelectVisibleText('#form_compiler', 'GNU C++ - 64bit');
    $this->clickButton('Trimite');
  }

  private function deleteLastSubmittedJob(): void {
    // Backend deletion -- there is no frontend for it.
    $id = $this->getLastJobId();
    Job::deleteById($id);
  }

}
