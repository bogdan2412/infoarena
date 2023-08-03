<?php

class TestTaskEditTags extends FunctionalTest {

  private \Facebook\WebDriver\WebDriverCheckboxes $checkboxes;

  function run(): void {
    $this->setup();
    $this->assertCheckboxStates([ true, false, true, false ]);
    $this->toggleAllCheckboxes();
    $this->assertCheckboxStates([ false, true, false, true ]);
    $this->toggleAllCheckboxes();
  }

  private function setup(): void {
    $this->login('intern', '1234');
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Taguri');
  }

  private function assertCheckboxStates(array $states): void {
    $this->collectCheckboxes();
    for ($i = 0; $i < 4; $i++) {
      $opt = $this->checkboxes->getOptions()[$i];
      if ($states[$i]) {
        $this->assertCheckboxChecked($opt);
      } else {
        $this->assertCheckboxUnchecked($opt);
      }
    }
  }

  private function collectCheckboxes(): void {
    $this->checkboxes = $this->getCheckboxesByCss('.tag_list input[type="checkbox"]');
  }

  private function toggleAllCheckboxes(): void {
    for ($i = 0; $i < 4; $i++) {
      $opt = $this->checkboxes->getOptions()[$i];
      if ($this->isCheckboxChecked($opt)) {
        $this->checkboxes->deselectByIndex($i);
      } else {
        $this->checkboxes->selectByIndex($i);
      }
    }
    $this->clickButton('Salvează');
    $this->assertTextExists('Am salvat tagurile.');
  }
}
