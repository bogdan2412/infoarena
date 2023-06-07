<?php

class Choice {
  static function selectFrom(array $choices): string {
    Log::default('Please make a choice:');
    foreach ($choices as $char => $description) {
      Log::default('%s   %s', [$char, $description], 1);
    }

    $keys = array_keys($choices);
    $prompt = sprintf('Your choice [%s]? ', implode('/', $keys));

    do {
      $choice = readline($prompt);
    } while (!empty($choices) && !in_array($choice, $keys));

    return $choice;
  }
}
