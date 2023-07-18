<?php

class FlashMessage {
  // An array of [$text, $type] pairs, where $type is one of (success, info, warning, error).
  static array $messages = [];

  static function add(string $message, string $type): void {
    self::$messages[] = [
      'text' => $message,
      'type' => $type
    ];
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

  static function getMessages(): array {
    return self::$messages;
  }

  static function saveToSession(): void {
    if (count(self::$messages)) {
      Session::set('flashMessages', self::$messages);
    }
  }

  static function restoreFromSession(): void {
    if ($messages = Session::get('flashMessages')) {
      self::$messages = $messages;
      Session::unsetVar('flashMessages');
    }
  }
}
