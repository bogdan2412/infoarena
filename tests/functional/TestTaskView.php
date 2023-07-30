<?php

class TestTaskView extends FunctionalTest {

  function run(): void {
    $this->testAnonCanViewPublic();
    $this->testAnonCannotViewPrivate();
    $this->testNormalCannotViewPrivate();
    $this->testOwnerHelperCanViewPrivate();
    $this->testInternCanViewPrivate();
  }

  private function testAnonCanViewPublic(): void {
    $this->ensureLoggedOut();
    $this->visitTaskPage('task1');
    $this->assertOnTaskPage('task1');
  }

  private function testAnonCannotViewPrivate(): void {
    $this->ensureLoggedOut();
    $this->visitTaskPage('task2');
    $this->assertLoginRequired();
  }

  private function testNormalCannotViewPrivate(): void {
    $this->login('normal', '1234');
    $this->visitTaskPage('task2');
    $this->assertPermissionError();
  }

  private function testOwnerHelperCanViewPrivate(): void {
    $this->login('helper', '1234');
    $this->visitTaskPage('task2');
    $this->assertOnTaskPage('task2');
  }

  private function testInternCanViewPrivate(): void {
    $this->login('intern', '1234');
    $this->visitTaskPage('task2');
    $this->assertOnTaskPage('task2');
  }

}
