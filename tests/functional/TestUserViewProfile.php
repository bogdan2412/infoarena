<?php

class TestUserViewProfile extends FunctionalTest {

  function run(): void {
    $this->login('normal', '1234');
    $this->visitUserProfile('admin');
    $this->assertTextExists('This is revision 5 of template/userheader.');
    $this->assertTextExists('Admin Admin (admin)');

    $this->clickLinkByText('Rating');
    $this->assertTextExists('This is revision 5 of template/userrating.');

    $this->clickLinkByText('Statistici');
    $this->assertTextExists('This is revision 5 of template/userstats.');
  }
}
