<?php

class TestTaskViewTags extends FunctionalTest {

  function run(): void {
    $this->testAnonCanViewPublic();
  }

  private function testAnonCanViewPublic(): void {
    $this->ensureLoggedOut();
    $this->visitTaskPage('task1');
    $this->clickLinkByText('Arată 2 categorii');
    $this->clickLinkByText('1 etichete');
    $this->clickLinkByText('1 etichete');
    $this->assertTextExists('category1');
    $this->assertTextExists('category2');
    $this->assertTextExists('tag1');
    $this->assertTextExists('tag3');
  }

}
