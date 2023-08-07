<?php

class TestJobView extends FunctionalTest {

  function run(): void {
    $this->testAnonCanViewPublic();
    $this->testAnonCannotViewPrivate();
    $this->testNormalCannotViewPrivate();
    $this->testHelperOwnerCanViewPrivate();
    $this->testAdminCanViewPrivate();
  }

  private function testAnonCanViewPublic(): void {
    $this->ensureLoggedOut();
    $this->visitJobPage(1);
    $this->assertTextExists('Borderou de evaluare (job #1)');
  }

  private function testAnonCannotViewPrivate(): void {
    $this->ensureLoggedOut();
    $this->visitJobPage(2);
    $this->assertLoginRequired();
  }

  private function testNormalCannotViewPrivate(): void {
    $this->login('normal', '1234');
    $this->visitJobPage(2);
    $this->assertPermissionError();
  }

  private function testHelperOwnerCanViewPrivate(): void {
    $this->login('helper', '1234');
    $this->visitJobPage(2);
    $this->assertTextExists('Borderou de evaluare (job #2)');
  }

  private function testAdminCanViewPrivate(): void {
    $this->login('admin', '1234');
    $this->visitJobPage(2);
    $this->assertTextExists('Borderou de evaluare (job #2)');
  }

}
