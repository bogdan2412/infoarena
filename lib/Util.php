<?php

class Util {

  static function redirect($location, $statusCode = 301): void {
    FlashMessage::saveToSession();
    header("Location: $location", true, $statusCode);
    exit;
  }

  static function redirectToHome(): void {
    self::redirect(Config::URL_PREFIX);
  }

  static function redirectToLogin(): void {
    if (!empty($_POST)) {
      Session::set('postData', $_POST);
    }
    FlashMessage::addWarning('Pentru această operație este nevoie să te autentifici.');
    Session::set('REAL_REFERRER', $_SERVER['REQUEST_URI']);
    self::redirect(Config::URL_PREFIX . 'login');
  }

  // Redirects to the same page, stripping any GET parameters but preserving
  // any slash-delimited arguments.
  static function redirectToSelf(): void {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    self::redirect($path);
  }

  // Looks up the referrer in $_REQUEST, then in $_SESSION, then in $_SERVER.
  // We sometimes need to pass the referrer in $_SESSION because PHP redirects
  // (in particular redirects to the login page) lose the referrer.
  static function getReferrer(): ?string {
    $referrer = Request::get('referrer');

    if ($referrer) {
      Session::unsetVar('REAL_REFERRER');
    } else {
      $referrer = Session::get('REAL_REFERRER') ?? $_SERVER['HTTP_REFERER'] ?? null;
    }

    return $referrer;
  }

}
