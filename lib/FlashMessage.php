<?php

class FlashMessage {
  static string $message;
  static string $cssClass;

  static function init(): void {
    self::getFromSession();
    self::clear();
  }

  private static function getFromSession(): void {
    init_php_session();
    self::$message = $_SESSION['_ia_flash'] ?? '';
    self::$cssClass =  $_SESSION['_ia_flash_class'] ?? '';
  }

  private static function clear(): void {
    unset($_SESSION['_ia_flash']);
    unset($_SESSION['_ia_flash_class']);
  }

  static function hasMessage(): bool {
    return self::$message != '';
  }

  static function getMessage(): string {
    return self::$message;
  }

  static function getCssClass(): string {
    return self::$cssClass;
  }
}
