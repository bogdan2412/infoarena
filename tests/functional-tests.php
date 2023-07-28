<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/vendor/autoload.php';
use Facebook\WebDriver\Exception\Internal\WebDriverCurlException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

$suite = new TestSuite();
$suite->run();
$suite->tearDown();
printf("Test suite completed with %d failures.\n", $suite->getNumFailures());

class TestSuite {
  const DRIVER_URL = 'http://localhost:4444';

  private RemoteWebDriver $driver;
  private string $homepageUrl;
  private int $numFailures = 0;

  function __construct() {
    $this->homepageUrl = Config::URL_HOST . Config::URL_PREFIX;
  }

  function getNumFailures() {
    return $this->numFailures;
  }

  function run(): void {
    $this->connectToDriver();
    $this->findAndRunTests();
  }

  private function connectToDriver(): void {
    $capabilities = DesiredCapabilities::firefox();

    try {
      $this->driver = RemoteWebDriver::create(self::DRIVER_URL, $capabilities);
    } catch (WebDriverCurlException $e) {
      print "Cannot start the web driver. Did you remember to run geckodriver?\n";
      print "If you are running functional-tests.php directly, please run functional-tests.sh instead.\n";
      exit;
    }
  }

  private function findAndRunTests(): void {
    $wildcard = __DIR__ . '/functional/Test*.php';
    $files = glob($wildcard);
    foreach ($files as $file) {
      $this->setupAndRunTest($file);
    }
  }

  private function setupAndRunTest(string $file): void {
    preg_match('|/([^/]+)\.php$|', $file, $match);
    $class = $match[1];
    printf("* Running %s...\n", $class);

    require_once $file;
    $test = new $class($this->driver, $this->homepageUrl);
    $this->runTest($test);
  }

  private function runTest(FunctionalTest $test): void {
    try {
      $test->run();
    } catch (Exception $e) {
      $this->numFailures++;
      printf("  * EXCEPTION: %s\n", $e->getMessage());
    }
  }

  function tearDown(): void {
    $this->driver->quit();
  }
}

abstract class FunctionalTest {
  protected RemoteWebDriver $driver;
  protected string $homepageUrl;

  function __construct(RemoteWebDriver $driver, string $homepageUrl) {
    $this->driver = $driver;
    $this->homepageUrl = $homepageUrl;
  }

  private function getElement(WebDriverBy $locator): RemoteWebElement {
    return $this->driver->findElement($locator);
  }

  protected function getLinkByText(string $text): RemoteWebElement {
    return $this->getElement(WebDriverBy::linkText($text));
  }

  protected function getElementByCss(string $cssSelector): RemoteWebElement {
    return $this->getElement(WebDriverBy::cssSelector($cssSelector));
  }

  protected function assert(bool $cond, string $errorMsg): void {
    if (!$cond) {
      throw new Exception($errorMsg);
    }
  }

  protected function assertLinkText(RemoteWebElement $link, string $expectedText): void {
    $actualText = $link->getText();
    $msg = sprintf('Expected link text [%s], found [%s]', $expectedText, $actualText);
    $this->assert($actualText == $expectedText, $msg);
  }

  protected function assertLinkUrl(RemoteWebElement $link, string $expectedUrl): void {
    $actualUrl = $link->getAttribute('href');
    $msg = sprintf('Expected link URL [%s], found [%s]', $expectedUrl, $actualUrl);
    $this->assert($actualUrl == $expectedUrl, $msg);
  }

  abstract function run(): void;
}
