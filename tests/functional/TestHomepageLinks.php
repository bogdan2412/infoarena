<?php

/**
 * Verifies that the homepage contains the usual links.
 **/

class TestHomepageLinks extends FunctionalTest {

  function run(): void {
    $this->ensureLoggedOut();
    $this->visitHomePage();

    // sidebar links
    $this->getLinkByText('Concursuri');
    $this->getLinkByText('Concursuri virtuale');
    $this->getLinkByText('Clasament');
    $this->getLinkByText('Monitorul de evaluare');
    $this->getLinkByText('Categorii probleme');
    $this->getLinkByText('Mă înregistrez!');
    $this->getLinkByText('Mi-am uitat parola...');

    // footer links
    $this->getLinkByText('Prima pagină');
    $this->getLinkByText('Despre ' . Config::SITE_NAME);
    $this->getLinkByText('Termeni și condiții');
    $this->getLinkByText('Contact');
  }
}
