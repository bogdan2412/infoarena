<?php

class Round extends Base {

  public static $_table = 'ia_round';

  function getUser(): ?User {
    return User::get_by_id($this->user_id) ?: null;
  }

  function getLastEdit(): ?string {
    $page = Textblock::get_by_name($this->page_name);
    if (!$page) {
      return null;
    }
    return $page->timestamp;
  }

  function hasNonstandardPage(): bool {
    return $this->page_name != 'runda/' . $this->id;
  }

  function countJobs(): int {
    return Job::countByRoundId($this->id);
  }

  function countTasks(): int {
    return RoundTask::countByRoundId($this->id);
  }

  function isEditable(): bool {
    return Identity::ownsRound($this);
  }

  static function deleteById(string $roundId): void {
    $round = Round::get_by_id($roundId);
    if (!$round) {
      FlashMessage::addError('Rundă inexistentă.');
      Util::redirectToHome();
    }
    $round->delete();
  }

  function delete(): void {
    // Also delete the round page and rankings page.
    textblock_delete($this->page_name);
    textblock_delete($this->page_name . '/clasament');
    round_delete($this->id);
  }

}
