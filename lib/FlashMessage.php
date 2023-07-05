<?php

class FlashMessage {
  const SESSION_VARIABLE = 'flashMessages';

  // an array of [$text, $type] pairs, where $type is one of (success, info, warning, error).
  static array $messages = [];
  static bool $hasErrors = false;

  static function add(string $message, string $type): void {
    self::$messages[] = [
      'text' => $message,
      'type' => $type
    ];
    self::$hasErrors |= ($type == 'error');
  }

  static function addSuccess(string $message): void {
    self::add($message, 'success');
  }

  static function addInfo(string $message): void {
    self::add($message, 'info');
  }

  static function addWarning(string $message): void {
    self::add($message, 'warning');
  }

  static function addError(string $message): void {
    self::add($message, 'error');
  }

  static function getMessages() {
    return self::$messages;
  }

  static function hasErrors() {
    return self::$hasErrors;
  }

  static function saveToSession() {
    if (count(self::$messages)) {
      init_php_session();
      $_SESSION[self::SESSION_VARIABLE] = self::$messages;
    }
  }

  static function restoreFromSession() {
    init_php_session();
    self::$messages = $_SESSION[self::SESSION_VARIABLE] ?? [];
    unset($_SESSION[self::SESSION_VARIABLE]);
  }
}
