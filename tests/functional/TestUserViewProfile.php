<?php

class TestUserViewProfile extends FunctionalTest {

  function run(): void {
    $this->login('normal', '1234');
    $this->assertLoggedInAs('normal');
    $this->visitUserProfile('admin');
    $this->assertTextExists(
      'This is the userheader template. This is revision 5.');
    $this->assertTextExists(
      'My username is admin. Here is something else about myself. This is revision 5.');
  }
}
