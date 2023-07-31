<?php

class TestTextblockRestore extends FunctionalTest {

  function run(): void {
    $this->testAnonCannotRestore();
    $this->testNormalCannotRestore();
    $this->restoreAsAdmin();
  }

  private function testAnonCannotRestore(): void {
    $this->ensureLoggedOut();
    $this->visitTextblockRestorePage('page-public', 2);
    $this->assertTextExists('Nu am putut înlocui pagina.');
  }

  private function testNormalCannotRestore(): void {
    $this->login('normal', '1234');
    $this->visitTextblockRestorePage('page-public', 2);
    $this->assertTextExists('Nu am putut înlocui pagina.');
  }

  private function restoreAsAdmin(): void {
    $this->login('admin', '1234');
    $this->changePage();
    $this->restorePage();
  }

  private function changePage(): void {
    $this->visitTextblockEditPage('page-public');
    $this->changeInput('#form_text', 'abcde');
    $this->clickButton('Salvează');
    $this->assertTextExists('abcde');
  }

  private function restorePage(): void {
    $this->clickLinkByText('Istoria');

    // There may have been other changes while exercising textblocks. Restore
    // the fifth oldest version.
    sleep(1);
    $links = $this->getLinksByText('Înlocuiește');
    $link = $links[count($links) - 5];
    $link->click();

    $this->assertTextExists('Am înlocuit pagina cu revizia 5.');
    $this->assertTextExists('This is revision 5 of page-public.');
  }

}
