<?php

/**
 * We try to determine if a user is logged in, in this order:
 * 1. from the userId session variable;
 * 2. from the long-lived login cookie.
 */

class Session {

  const SESSION_NAME = 'nerdarena-session';
  const ONE_YEAR_IN_SECONDS = 365 * 86400;

  static function init(): void {
    session_name(self::SESSION_NAME);

    if (isset($_COOKIE[self::SESSION_NAME])) {
      self::start();
    }

    if (Request::isWeb()) {
      self::setActiveUser();
    }
    // Otherwise we're being called by a command line script.
  }

  private static function start(): void {
    session_start();
  }

  private static function setActiveUser(): void {
    if ($userId = self::get('userId')) {
      if (!Identity::set($userId)) {
        // the underlying user is gone, e.g. if the development database was reimported
        self::unsetVar('userId');
      }
    } else {
      self::loadUserFromCookie();
    }
  }

  // If we have a valid long lived login cookie, transfer it to the session.
  private static function loadUserFromCookie(): void {
    $lll = $_COOKIE['login'] ?? null;
    if ($lll) {
      $cookie = Cookie::get_by_string($lll);
      $user = $cookie ? User::get_by_id($cookie->userId) : null;
      if ($user) {
        self::set('userId', $user->id);
        Identity::set($user->id);
      } else {
        // The cookie is invalid.
        self::unsetCookie('login');
        unset($_COOKIE['login']);
        if ($cookie) {
          $cookie->delete();
        }
      }
    }
  }

  static function login(User $user, bool $remember = false, ?string $referrer = null): void {
    self::set('userId', $user->id);
    if ($remember) {
      $cookie = Cookie::create($user->id);
      setcookie('login', $cookie->string, time() + self::ONE_YEAR_IN_SECONDS, '/');
    }

    log_print($user->username . ' logged in, IP=' . $_SERVER['REMOTE_ADDR']);

    $postData = self::get('postData');

    if (!$referrer) {
      Util::redirectToHome();
    } else if (empty($postData)) {
      Util::redirect($referrer);
    } else {
      // print the post data in a form and submit it with javascript
      Smart::assign([
        'postData' => $postData,
        'referrer' => $referrer,
      ]);

      self::unsetVar('postData');
      Smart::display('auth/repost.tpl');
      exit;
    }
  }

  static function logout(): void {
    log_print(Identity::getUsername() . ' logged out, IP=' . $_SERVER['REMOTE_ADDR']);
    $string = $_COOKIE['login'] ?? '';
    $cookie = Cookie::get_by_string($string);
    if ($cookie) {
      $cookie->delete();
    }
    self::unsetCookie('login');
    unset($_COOKIE['login']);
    self::kill();
    Util::redirectToHome();
  }

  static function get(string $name, mixed $default = null) {
    return $_SESSION[$name] ?? $default;
  }

  static function set($var, $value): void {
    // Lazy start of the session so we don't send a PHPSESSID cookie unless we have to
    if (!isset($_SESSION)) {
      self::start();
    }
    $_SESSION[$var] = $value;
  }

  static function unsetVar($var): void {
    if (isset($_SESSION)) {
      unset($_SESSION[$var]);
      if (!count($_SESSION)) {
        // Note that this will prevent us from creating another session this same request.
        // This does not seem to cause a problem at the moment.
        self::kill();
      }
    }
  }

  static function unsetCookie($name): void {
    unset($_COOKIE[$name]);
    setcookie($name, '', time() - 3600, '/');
  }

  static function has($var): bool {
    return isset($_SESSION) && isset($_SESSION[$var]);
  }

  private static function kill(): void {
    if (!isset($_SESSION)) {
      self::start(); // It has to have been started in order to be destroyed.
    }
    session_unset();
    @session_destroy();
    if (ini_get("session.use_cookies")) {
      self::unsetCookie(session_name());
    }
  }


}
