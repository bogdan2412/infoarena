<?php

class TestTaskSubmit extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotSubmit();
    $this->testNormalCannotSubmitToPrivateTask();
    $this->testHelperCanSubmitToOwnTask();
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

}
