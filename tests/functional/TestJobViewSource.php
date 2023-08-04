<?php

class TestJobViewSource extends FunctionalTest {

  function run(): void {
    $this->testAnonViewOpen();
    $this->changeTaskSourceAccess('Nu');
    $this->testAnonViewClosed();
    $this->testNormalViewClosed();
    $this->testHelperViewClosed();
    $this->changeTaskSourceAccess('Da');
  }

  private function testAnonViewOpen(): void {
    $this->ensureLoggedOut();
    $this->assertSourceVisible(1, 's1.cpp');
    $this->assertSourceRequiresLogin(2);
    $this->assertSourceVisible(3, 's1.cpp');
    $this->assertSourceVisible(4, 's1.cpp');
    $this->assertSourceVisible(5, 's2.cpp');
    $this->assertSourceVisible(6, 's3.c');
    $this->assertSourceVisible(7, 's4.c');
  }

  private function testAnonViewClosed(): void {
    $this->ensureLoggedOut();
    $this->assertSourceRequiresLogin(1);
    $this->assertSourceRequiresLogin(5);
  }

  private function testNormalViewClosed(): void {
    $this->login('normal', '1234');
    $this->assertSourcePermissionError(1);
    $this->assertSourcePermissionError(2);
    $this->assertSourceNotVisible(3);
    $this->assertSourceNotVisible(4);
    $this->assertSourceNotVisible(5);
    $this->assertSourceNotVisible(6);
    $this->assertSourceVisible(7, 's4.c');
  }

  private function testHelperViewClosed(): void {
    $this->login('normal', '1234');
    $this->assertSourcePermissionError(1);
    $this->assertSourceNotVisible(2);
    $this->assertSourceNotVisible(3);
    $this->assertSourceNotVisible(4);
    $this->assertSourceNotVisible(5);
    $this->assertSourceNotVisible(6, 's3.c');
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
