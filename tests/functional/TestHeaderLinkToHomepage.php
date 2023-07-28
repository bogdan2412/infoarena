<?php

/**
 * Verifies that the header on every page contains a link to the homepage.
 **/

class TestHeaderLinkToHomepage extends FunctionalTest {

  function run(): void {
    $this->driver->get($this->homepageUrl);

    $someOtherPage = $this->getLinkByText('Monitorul de evaluare');
    $someOtherPage->click();

    $link = $this->getElementByCss('#header h1 a');
    $expectedText = Config::SITE_NAME . ' — informatică de performanță';
    $this->assertLinkText($link, $expectedText);
    $this->assertLinkUrl($link, Config::URL_PREFIX);
  }
}
