<?php

/**
 * Creates and stores resized images.
 **/

class Image {

  const CACHE_LIFE = 2592000; // 30 days in seconds.
  const RESIZED_PATH = Config::ROOT . 'www/static/images/resized/';

  const FILE_TYPE_SUBSTRINGS = [
    'gif' => 'GIF image data',
    'jpg' => 'JPEG image data',
    'png' => 'PNG image data',
    'svg' => 'SVG Scalable Vector Graphics image',
  ];

  private static function getFingerprint(string $fullPath, string $geometry): string {
    return md5($fullPath . '-' . $geometry);
  }

  private static function getExtension(string $fullPath): string {
    OS::execute("file {$fullPath}", $output);
    foreach (self::FILE_TYPE_SUBSTRINGS as $ext => $substring) {
      if (str_contains($output, $substring)) {
        return '.' . $ext;
      }
    }
    return '';
  }

  static function isImage($fullPath): bool {
    return self::getExtension($fullPath) != '';
  }

  private static function getResizedFileName(string $fullPath, string $geometry): string {
    $fp = self::getFingerprint($fullPath, $geometry);
    $ext = self::getExtension($fullPath);
    return $fp . $ext;
  }

  private static function needsRegenerating(string $relResizedName, string $fullPath): bool {
    $timestamp = @filemtime(self::RESIZED_PATH . $relResizedName);
    $fullTimestamp = @filemtime($fullPath);
    $now = time();
    return
      !$timestamp ||
      ($timestamp < $now - self::CACHE_LIFE) ||
      ($timestamp < $fullTimestamp);
  }

  private static function generateImage(string $origName, string $relResizedName,
                                        string $geometry): void {
    $fullResizedPath = self::RESIZED_PATH . $relResizedName;
    if (Str::endsWith($relResizedName, '.svg')) {
      // Do not resize SVG images.
      copy($origName, $fullResizedPath);
    } else {
      $cmd = sprintf('convert -resize %s -sharpen 1x1 %s %s',
                     $geometry, $origName, $fullResizedPath);
      OS::execute($cmd, $output);
    }
  }

  // Creates a resized image unless it exists and is fresh.
  // Returns its full path.
  static function resize(string $fullPath, string $geometry): string {
    $relResizedName = self::getResizedFileName($fullPath, $geometry);

    if (self::needsRegenerating($relResizedName, $fullPath)) {
      self::generateImage($fullPath, $relResizedName, $geometry);
    }

    return self::RESIZED_PATH . $relResizedName;
  }
}
