<?php

class TestDescriptorParser {
  const MAX_VALUE = 1000; // sanity check

  static function makeOneToOneMapping(int $numTests): array {
    $result = [];
    for ($i = 1; $i <= $numTests; $i++) {
      $result[$i] = [ $i ];
    }
    return $result;
  }

  static function parseTestGroups(string $descriptor): array {
    $groups = [];
    $numGroups = 0;
    $parts = explode(';', $descriptor);
    foreach ($parts as $part) {
      $groups[++$numGroups] = TestDescriptorParser::parseTestGroup($part);
    }
    return $groups;
  }

  static function parseTestGroup(string $descriptor): array {
    $result = self::collectInts($descriptor);
    sort($result);
    self::checkUniqueness($result);
    return $result;
  }

  private static function collectInts(string $descriptor): array {
    $result = [];
    $parts = explode(',', $descriptor);
    foreach ($parts as $rangeOrInteger) {
      $tests = self::parseRangeOrInteger($rangeOrInteger);
      array_push($result, ...$tests);
    }

    return $result;
  }

  private static function parseRangeOrInteger(string $rangeOrInteger): array {
    $parts = explode('-', $rangeOrInteger);
    if (count($parts) >= 3) {
      $msg = "Prea multe caractere „-” în intervalul $rangeOrInteger.";
      throw new TestDescriptorException($msg);
    }

    return (count($parts) == 2)
      ? self::parseRange($parts[0], $parts[1])
      : [ self::parseInt($parts[0]) ];
  }

  private static function parseRange(string $low, string $high): array {
    $low = self::parseInt($low);
    $high = self::parseInt($high);

    if ($low > $high) {
      throw new TestDescriptorException("Inversiune în intervalul $low-$high.");
    }

    return range($low, $high);
  }

  private static function parseInt(string $val): int {
    $val = trim($val);

    if ($val === '') {
      throw new TestDescriptorException('Numerele trebuie să conțină cel puțin o cifră.');
    }

    if (!ctype_digit($val)) {
      throw new TestDescriptorException("Numărul [$val] conține altceva decît cifre.");
    }

    $val = (int)$val;

    if ($val > self::MAX_VALUE) {
      throw new TestDescriptorException("Numărul $val este prea mare.");
    }

    return $val;
  }

  private static function checkUniqueness(array $ints) {
    for ($i = 1; $i < count($ints); $i++) {
      if ($ints[$i] == $ints[$i - 1]) {
        throw new TestDescriptorException("Valoarea $ints[$i] este duplicată.");
      }
    }
  }
}
