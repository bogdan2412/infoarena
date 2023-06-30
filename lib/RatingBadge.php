<?php

require_once __DIR__ . '/../common/rating.php';
require_once __DIR__ . '/../www/format/format.php';

class RatingBadge {

  private string $username;
  private float $rating;
  private bool $isAdmin;

  function __construct(string $username, float $rating) {
    $this->username = $username;
    $this->rating = rating_scale($rating);
    $this->isAdmin = user_is_admin(user_get_by_username($username));
  }

  function getUsername(): string {
    return $this->username;
  }

  function getRating(): float {
    return $this->rating;
  }

  function getRatingClass(): int {
    $rec = rating_group($this->rating, $this->isAdmin);
    return $rec['group'];
  }
}
