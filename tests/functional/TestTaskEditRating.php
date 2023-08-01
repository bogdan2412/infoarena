<?php

class TestTaskEditRating extends FunctionalTest {

  function run(): void {
    $this->editAsAdmin();
  }

  private function editAsAdmin(): void {
    $this->login('admin', '1234');
    $this->editRatings();
    $this->verifyRatings();
  }

  private function editRatings(): void {
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Ratinguri');
    $this->changeInput('#form_idea', '3');
    $this->changeInput('#form_theory', '4');
    $this->changeInput('#form_coding', '5');
    $this->clickButton('Salvează');
    $this->assertTextExists('Am salvat ratingurile.');
  }

  private function verifyRatings(): void {
    $this->assertInputValue('#form_idea', '3');
    $this->assertInputValue('#form_theory', '4');
    $this->assertInputValue('#form_coding', '5');
    $this->visitTaskPage('task1');
    $rating = $this->getHiddenElementText('.star-rating .hidden');
    $this->assert($rating == '3/5',
                  "Expected a rating of 3/5, found {$rating}.");
  }

}
