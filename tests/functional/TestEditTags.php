<?php

class TestEditTags extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotEditTags();
    $this->testHelperCannotEditTags();
    $this->internAddCategoryAndTags();
    $this->verifyCategoryAndTags();
    $this->deleteCategoryAndTags();
    $this->verifyNoCategoryOrTags();
  }

  private function testAnonCannotEditTags(): void {
    $this->ensureLoggedOut();
    $this->visitTagManagerPage();
    $this->assertLoginRequired();
  }

  private function testHelperCannotEditTags(): void {
    $this->login('helper', '1234');
    $this->visitTagManagerPage();
    $this->assertPermissionError();
  }

  private function internAddCategoryAndTags(): void {
    $this->login('intern', '1234');
    $this->visitTagManagerPage();

    $this->clickLinkByText('Adaugă categorie nouă');
    $this->changeInput('input[name="name"]', 'abcd');
    $this->clickButton('Adaugă');

    $this->clickLinkByText('Adaugă tag nou');
    $this->changeInput('li.algorithm_tag_add input[name="name"]', 'efgh');
    $this->getElementByCss('li.algorithm_tag_add input[type="submit"]')->click();

    $this->clickLinkByText('Adaugă tag nou');
    $this->changeInput('li.algorithm_tag_add input[name="name"]', 'ijkl');
    $this->getElementByCss('li.algorithm_tag_add input[type="submit"]')->click();
  }

  private function verifyCategoryAndTags(): void {
    $this->assertTextExists('abcd');
    $this->assertTableCellText('table.alternating-colors', 1, 1, 'efgh');
    $this->assertTableCellText('table.alternating-colors', 2, 1, 'ijkl');
  }

  private function deleteCategoryAndTags(): void {
    $this->clickLinkByText('Șterge categorie'); // will delete tags as well
  }

  private function verifyNoCategoryOrTags(): void {
    $this->assertNoText('abcd');
    $this->assertNoText('efgh');
    $this->assertNoText('ijkl');
  }

}
