<?php

/**
 * Verifies that the header on every page contains a link to the homepage.
 **/

class TestHeaderLinkToHomepage extends FunctionalTest {

  function run(): void {
    $this->visitHomePage();

    $this->clickLinkByText('Monitorul de evaluare');

    $link = $this->getElementByCss('#header a.homepage-link');
    $expectedText = Config::SITE_NAME . ' — informatică de performanță';
    $this->assertLinkText($link, $expectedText);
    $this->assertLinkUrl($link, Config::URL_PREFIX);
  }
}
