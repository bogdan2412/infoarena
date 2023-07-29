<?php

/**
 * Logins as every role and performs basic checks.
 **/

class TestLoginAsAllRoles extends FunctionalTest {

  const USERNAMES = ['admin', 'intern', 'helper', 'normal'];

  function run(): void {
    $this->visitMonitorPage();

    foreach (self::USERNAMES as $username) {
      $this->login($username, '1234');
    }
  }
}
