<?php

class TestUserBan extends FunctionalTest {

  function run(): void {
    $this->testHelperCannotBan();
    $this->testAdminBan();
    $this->testInternCannotLogin();
    $this->testAdminUnban();
  }

  private function testHelperCannotBan(): void {
    $this->login('helper', '1234');
    $this->visitUserProfile('intern');
    $this->assertNoLink('blochează');

    // Try to block the user anyway.
    $intern = User::get_by_username('intern');
    $relativeUrl = url_user_control($intern->id);
    $absoluteUrl = Config::URL_HOST . $relativeUrl;
    $this->driver->get($absoluteUrl);
    $this->assertOnHomePage();
  }

  private function testAdminBan(): void {
    $this->login('admin', '1234');
    $this->visitUserProfile('intern');
    $this->assertNoText('Acest utilizator este blocat.');

    $this->clickLinkByText('blochează');
    $this->assertTextExists('Acest utilizator este blocat.');
  }

  private function testInternCannotLogin(): void {
    $this->ensureLoggedOut();
    $this->fillLoginForm('intern', '1234');
    $this->assertOnHomePage();
    $this->assertTextExists('Contul tău este blocat. Dacă nu știm noi de ce, știi tu.');
  }

  private function testAdminUnban(): void {
    $this->login('admin', '1234');
    $this->visitUserProfile('intern');
    $this->clickLinkByText('deblochează');
    $this->assertNoText('Acest utilizator este blocat.');
  }
}
