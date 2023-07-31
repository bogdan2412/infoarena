<?php

class TestChangesView extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotView();
    $this->testHelperCannotView();
    $this->testAdminCanView();
  }

  private function testAnonCannotView(): void {
    $this->ensureLoggedOut();
    $this->visitChangesPage();
    $this->assertLoginRequired();
  }

  private function testHelperCannotView(): void {
    $this->login('helper', '1234');
    $this->visitChangesPage();
    $this->assertPermissionError();
  }

  private function testAdminCanView(): void {
    $this->login('admin', '1234');
    $this->visitChangesPage();
    $this->assertTextExists('Ultimele modificări');
  }
}
