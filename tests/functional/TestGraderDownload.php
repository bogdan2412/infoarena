<?php

class TestGraderDownload extends FunctionalTest {

  function run(): void {
    $this->testAnonCanViewOpenTests();
    $this->makeTaskClosed();
    $this->testAnonCannotViewClosedTests();
    $this->makeTaskOpen();
  }

  private function testAnonCanViewOpenTests(): void {
    $this->ensureLoggedOut();
    $this->visitTaskPage('task1');
    $this->clickLinkByText('Listează atașamente');
    $this->assertLinkDownloadsContent('grader_test1.in', 'grader_test1.in');
  }

  private function makeTaskClosed(): void {
    $this->login('admin', '1234');
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_open_tests', 'Nu');
    $this->clickButton('Salvează');
  }

  private function testAnonCannotViewClosedTests(): void {
    $this->ensureLoggedOut();
    $this->visitAttachmentList('problema/task1');
    $this->clickLinkByText('grader_test1.in');
    $this->assertOnTaskPage('task1');
    $this->assertTextExists('Nu aveți permisiuni pentru a descărca fișierul grader_test1.in');
  }

  private function makeTaskOpen(): void {
    $this->login('admin', '1234');
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Parametri');
    $this->changeSelect('#form_open_tests', 'Da');
    $this->clickButton('Salvează');
  }

}
