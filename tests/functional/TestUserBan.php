<?php

class TestUserBan extends FunctionalTest {

  function run(): void {
    $this->testHelperCannotBan();
    $this->testAdminCanBan();
  }

  private function testHelperCannotBan() {
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

  private function testAdminCanBan() {
    $this->login('admin', '1234');
    $this->visitUserProfile('intern');
    $this->assertNoText('Acest utilizator este blocat.');

    $this->clickLinkByText('blochează');
    $this->assertTextExists('Acest utilizator este blocat.');

    $this->clickLinkByText('deblochează');
    $this->assertNoText('Acest utilizator este blocat.');
  }
}
