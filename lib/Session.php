<?php

/**
 * We try to determine if a user is logged in, in this order:
 * 1. from the userId session variable;
 * 2. from the HTTP auth headers (needed for the eval);
 * 3. from the long-lived login cookie.
 */

require_once __DIR__ . '/../common/user.php';

class Session {

  const SESSION_COOKIE = 'nerdarena-session';
  const LOGIN_COOKIE = 'nerdarena-login';
  const ONE_MONTH_IN_SECONDS = 30 * 86400;

  static function init(): void {
    session_name(self::SESSION_COOKIE);

    if (isset($_COOKIE[self::SESSION_COOKIE])) {
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
    } else if (self::hasHttpHeaders()) {
      self::loadUserFromHttpHeaders();
    } else {
      self::loadUserFromCookie();
    }
  }

  private static function hasHttpHeaders(): bool {
    return
      isset($_SERVER['PHP_AUTH_USER']) &&
      isset($_SERVER['PHP_AUTH_PW']);
  }

  private static function loadUserFromHttpHeaders(): void {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
    $user = User::getByUsernamePlainPassword($username, $password);
    if ($user) {
      self::set('userId', $user->id);
      Identity::set($user->id);
    }
  }

  // If we have a valid long lived login cookie, transfer it to the session.
  private static function loadUserFromCookie(): void {
    $cookieVal = $_COOKIE[self::LOGIN_COOKIE] ?? null;
    if ($cookieVal) {
      $user = Cookie::getUser($cookieVal);
      if ($user) {
        self::set('userId', $user->id);
        Identity::set($user->id);
      } else {
        // The cookie is invalid.
        self::unsetLoginCookie();
      }
    }
  }

  static function login(User $user, bool $remember = false, ?string $referrer = null): void {
    self::set('userId', $user->id);
    if ($remember) {
      $cookie = Cookie::create($user->id);
      setcookie(self::LOGIN_COOKIE, $cookie->string, time() + self::ONE_MONTH_IN_SECONDS, '/');
    }

    FlashMessage::addSuccess('Bine ai venit!');
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

    self::unsetLoginCookie();
    self::kill();
    Util::redirectToHome();
  }

  static function cleanupAndRedirectBannedUser(User $user): void {
    Cookie::delete_all_by_userId($user->id);
    self::unsetLoginCookie();
    self::unsetVar('userId');
    FlashMessage::addError('Contul tău este blocat. Dacă nu știm noi de ce, știi tu.');
    Util::redirectToHome();
  }

  static function get(string $name, $default = null) {
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
    }
  }

  private static function unsetCookie($name): void {
    unset($_COOKIE[$name]);
    setcookie($name, '', time() - 3600, '/');
  }

  private static function unsetLoginCookie(): void {
    if (isset($_COOKIE[self::LOGIN_COOKIE])) {
      Cookie::delete_all_by_string($_COOKIE[self::LOGIN_COOKIE]);
      self::unsetCookie(self::LOGIN_COOKIE);
    }
  }

  static function has($var): bool {
    return isset($_SESSION[$var]);
  }

  private static function kill(): void {
    $_SESSION = []; // unset all variables
    if (ini_get("session.use_cookies")) {
      self::unsetCookie(self::SESSION_COOKIE);
    }
  }
}
