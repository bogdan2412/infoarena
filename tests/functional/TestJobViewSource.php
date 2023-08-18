<?php

class TestJobViewSource extends FunctionalTest {

  function run(): void {
    $this->testAnonViewNothing();
    $this->changeTaskSourceAccess('Nu');
    $this->testAnonViewNothing();
    $this->testNormalViewClosed();
    $this->testHelperViewClosed();
    $this->changeTaskSourceAccess('Da');
  }

  private function testAnonViewNothing(): void {
    $this->ensureLoggedOut();

    // They are all part of an upcoming round.
    for ($i = 1; $i <= 8; $i++) {
      $this->assertSourceRequiresLogin($i);
    }
  }

  private function testNormalViewClosed(): void {
    $this->login('normal', '1234');
    $this->assertSourcePermissionError(1);
    $this->assertSourcePermissionError(2);
    $this->assertSourcePermissionError(3);
    $this->assertSourcePermissionError(4);
    $this->assertSourcePermissionError(5);
    $this->assertSourcePermissionError(6);
    $this->assertSourceVisible(7, 's4.c');
  }

  private function testHelperViewClosed(): void {
    $this->login('helper', '1234');
    $this->assertSourcePermissionError(1);
    $this->assertSourceNotVisible(2);
    $this->assertSourceNotVisible(3);
    $this->assertSourceNotVisible(4);
    $this->assertSourceNotVisible(5);
    $this->assertSourceVisible(6, 's3.c');
    $this->assertSourceNotVisible(7);
  }

  private function assertSourceVisible(int $jobId, string $fileName): void {
    $expectedMsg = sprintf('This is source %s', $fileName);
    $this->visitJobSourcePage($jobId);
    $this->assertTextExists($expectedMsg);
  }

  private function assertSourceNotVisible(int $jobId): void {
    $this->visitJobSourcePage($jobId);
    $this->assertNoText('int main');
  }

  private function assertSourceRequiresLogin(int $jobId): void {
    $this->visitJobSourcePage($jobId);
    $this->assertLoginRequired();
  }

  private function assertSourcePermissionError(int $jobId): void {
    $this->visitJobSourcePage($jobId);
    $this->assertPermissionError();
  }

  private function changeTaskSourceAccess(string $yesOrNo): void {
    $this->login('admin', '1234');
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_open_source', $yesOrNo);
    $this->clickButton('Salvează');
  }

}
