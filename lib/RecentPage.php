<?php

class RecentPage {
  const NUM_PAGES = 5;

  private string $url;
  private string $title;
  private bool $active;
  private static array $pages = [];

  function __construct(string $url, string $title, bool $active) {
    $this->url = $url;
    $this->title = $title;
    $this->active = $active;
  }

  function getUrl(): string {
    return $this->url;
  }

  function getTitle(): string {
    return $this->title;
  }

  function isActive(): string {
    return $this->active;
  }

  function setActive(): void {
    $this->active = true;
  }

  static function restoreFromSession(): void {
    $data = Session::get('recentPages', []);
    foreach ($data as $pair) {
      self::$pages[] = new RecentPage($pair[0], $pair[1], false);
    }
  }

  private static function saveToSession(): void {
    $data = [];
    foreach (self::$pages as $rp) {
      $key = strtolower($rp->url); // for historic reasons
      $data[$key] = [ $rp->url, $rp->title ];
    }
    Session::set('recentPages', $data);
  }

  private static function isWorthKeeping(string $url): bool {
    return
      !preg_match('/\/(json|changes)\//', $url) &&
      !Request::isPost();
  }

  private static function lookup(string $url): int {
    $i = count(self::$pages) - 1;

    while ($i >= 0 && self::$pages[$i]->getUrl() != $url) {
      $i--;
    }

    return $i;
  }

  static function addCurrentPage($title): void {
    $url = url_from_args($_GET);
    if (self::isWorthKeeping($url)) {
      $index = self::lookup($url);

      if ($index >= 0) {
        self::$pages[$index]->setActive();
      } else {
        self::$pages[] = new RecentPage($url, $title, true);
      }
    }

    while (count(self::$pages) > self::NUM_PAGES) {
      array_shift(self::$pages);
    }

    self::saveToSession();
  }

  static function getAll(): array {
    return self::$pages;
  }
}
