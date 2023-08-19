<?php

class TaskTests {

  protected int $numTests;
  protected array $testGroup = []; // test number => group number
  protected array $groups;
  private array $publicTests;

  // When used during task editing, throws TaskTestsException on bad group descriptions.
  function __construct(Task $task) {
    $this->numTests = $task->test_count;

    $this->groups = $task->test_groups
      ? TestDescriptorParser::parseTestGroups($task->test_groups, $this->numTests)
      : TestDescriptorParser::makeOneToOneMapping($this->numTests);

    $this->publicTests = $task->public_tests
      ? TestDescriptorParser::parseTestGroup($task->public_tests, $this->numTests)
      : [];

    $this->computeGroupOfEachTest();
    $this->checkThatAllTestsAreIncluded();
  }

  private function computeGroupOfEachTest(): void {
    foreach ($this->groups as $groupNo => $tests) {
      foreach ($tests as $test) {
        if (isset($this->testGroup[$test])) {
          throw new TestDescriptorException("Testul $test apare în mai multe grupe.");
        }
        $this->testGroup[$test] = $groupNo;
      }
    }
  }

  private function checkThatAllTestsAreIncluded(): void {
    for ($i = 1; $i <= $this->numTests; $i++) {
      if (!isset($this->testGroup[$i])) {
        throw new TestDescriptorException("Testul $i nu este cuprins în niciun grup.");
      }
    }
  }

  function getGroups(): array {
    return $this->groups;
  }

  function hasGroups(): bool {
    return count($this->groups) < $this->numTests;
  }

  function getGroupCssClass(int $groupNo): string {
    if (!$this->hasGroups()) {
      return '';
    } else if ($groupNo % 2) {
      return 'color0';
    } else {
      return 'color1';
    }
  }

  function hasPublicTests(): bool {
    $n = count($this->publicTests);
    return ($n > 0) && ($n < $this->numTests);
  }

  function isPublicTest(int $testNo): bool {
    return in_array($testNo, $this->publicTests);
  }
}
