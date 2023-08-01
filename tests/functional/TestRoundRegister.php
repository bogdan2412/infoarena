<?php

class TestRoundRegister extends FunctionalTest {

  function run(): void {
    $this->testCannotRegisterToArchive();
    $this->testRegisterToUpcomingRound();
  }

  private function testCannotRegisterToArchive(): void {
    $this->login('intern', '1234');
    $this->visitRoundPage('round-archive');
    $this->assertNoText('Înscrie-te acum');
  }

  private function testRegisterToUpcomingRound(): void {
    $this->login('normal', '1234');
    $this->visitRoundPage('round-classic');

    $this->assertTextExists('Nu ești înscris');
    $this->clickLinkByText('Înscrie-te acum!');
    $this->clickButton('Confirmă înscrierea');

    $this->assertTextExists('Te-ai înscris');
    $this->clickLinkByText('Vezi cine s-a mai înscris');
    $elem = $this->getElementByCss('.registered-users .fullname');
    $this->assert($elem->getText() == 'Normal Normal',
                  'Expected to find Normal Normal among registered users.');

    $this->driver->navigate()->back();
    $this->clickLinkByText('aici');
    $this->clickButton('Confirmă dezînscrierea');
    $this->assertTextExists('Nu ești înscris');
    $this->clickLinkByText('Vezi cine e înscris');
    $this->assertTextExists('Nici un utilizator înscris la această rundă');
  }

}
