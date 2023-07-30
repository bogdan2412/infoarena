<?php

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../common/log.php';
require_once __DIR__ . '/../www/identity.php';
require_once __DIR__ . '/../www/url.php';
require_once __DIR__ . '/../lib/Core.php';
require_once __DIR__ . '/vendor/autoload.php';

use Facebook\WebDriver\Exception\Internal\WebDriverCurlException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

if (!Config::DEVELOPMENT_MODE || !Config::TESTING_MODE) {
  print "To run functional tests, please set DEVELOPMENT_MODE = true and TESTING_MODE = true " .
    "in Config.php\n";
  exit;
}

db_connect();

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
    $capabilities = $this->getFirefoxCapabilities();

    try {
      $this->driver = RemoteWebDriver::create(self::DRIVER_URL, $capabilities);
    } catch (WebDriverCurlException $e) {
      print "Cannot start the web driver. Did you remember to run geckodriver?\n";
      print "If you are running functional-tests.php directly, please run functional-tests.sh instead.\n";
      exit;
    }
  }

  private function getFirefoxCapabilities(): DesiredCapabilities {
    $firefoxOptions = new FirefoxOptions();
    $firefoxOptions->addArguments(['-headless']);
    $capabilities = DesiredCapabilities::firefox();
    $capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
    return $capabilities;
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

  protected function getElementByXpath(string $xpath): RemoteWebElement {
    return $this->getElement(WebDriverBy::xpath($xpath));
  }

  protected function getSelectByCss(string $css): WebDriverSelect {
    $elem = $this->getElementByCss($css);
    return new WebDriverSelect($elem);
  }

  protected function getIdentityUsername(): string {
    try {
      $link = $this->getElementByCss('#active-username a');
      return $link->getText();
    } catch (NoSuchElementException $e) {
      return '';
    }
  }

  protected function visitMonitorPage(): void {
    $this->driver->get(Config::URL_HOST . url_monitor());
  }

  protected function visitTaskPage(string $taskId): void {
    $this->driver->get(Config::URL_HOST . url_task($taskId));
  }

  protected function visitTaskEditPage(string $taskId): void {
    $this->driver->get(Config::URL_HOST . url_task_edit($taskId));
  }

  protected function visitUserProfile(string $username): void {
    $this->driver->get(url_user_profile($username));
  }

  protected function visitUserAccount(string $username): void {
    $url = Config::URL_HOST . url_account($username);
    $this->driver->get($url);
  }

  protected function visitOwnAccount(): void {
    $url = Config::URL_HOST . url_account();
    $this->driver->get($url);
  }

  protected function clickLinkByText(string $text): void {
    $link = $this->getLinkByText($text);
    $link->click();
  }

  protected function clickButton(string $value): void {
    $xpath = "//input[@value='{$value}']";
    $input = $this->getElementByXpath($xpath);
    $input->click();
  }

  protected function changeInput(string $css, string $text): void {
    $elem = $this->getElementByCss($css);
    $elem->clear();
    $elem->sendKeys($text);
  }

  protected function changeSelect(string $css, string $visibleText): void {
    $sel = $this->getSelectByCss($css);
    $sel->selectByVisibleText($visibleText);
  }

  protected function ensureLoggedOut() {
    $identity = $this->getIdentityUsername();
    if ($identity) {
      $this->clickLinkByText('logout');
    }
  }

  protected function login(string $username, string $password) {
    $identity = $this->getIdentityUsername();
    if ($identity == $username) {
      return; // already logged in
    }

    $this->ensureLoggedOut();
    $this->getElementByCss('#form_username')->sendKeys($username);
    $this->getElementByCss('#form_password')->sendKeys($password);
    $this->getElementByCss('#form_submit')->click();

    $this->assertLoggedInAs($username);
  }

  protected function assert(bool $cond, string $errorMsg): void {
    if (!$cond) {
      throw new Exception($errorMsg);
    }
  }

  protected function assertTextExists(string $text): void {
    $quoted = addslashes($text);
    $xpath = "//*[contains(text(),'{$quoted}')]";
    try {
      $elem = $this->getElementByXpath($xpath);
    } catch (NoSuchElementException $e) {
      throw new Exception("Text not found in page: [{$text}].");
    }
  }

  protected function assertNoText(string $text): void {
    $quoted = addslashes($text);
    $xpath = "//*[contains(text(),'{$quoted}')]";
    try {
      $this->getElementByXpath($xpath);
      throw new Exception("Unwanted text found: [{$text}].");
    } catch (NoSuchElementException $e) {
    }
  }

  protected function assertNoElement(string $css): void {
    try {
      $this->getElementByCss($css);
      throw new Exception("Unwanted element found: [{$css}].");
    } catch (NoSuchElementException $e) {
    }
  }

  protected function assertInputValue(string $css, string $expectedValue): void {
    $elem = $this->getElementByCss($css);
    $actualValue = $elem->getAttribute('value');
    $msg = sprintf('Expected value [%s] for input [%s], found [%s].',
                   $expectedValue, $css, $actualValue);
    $this->assert($actualValue == $expectedValue, $msg);
  }

  protected function assertSelectVisibleText(string $css, string $expectedText): void {
    $sel = $this->getSelectByCss($css);
    $actualText = $sel->getFirstSelectedOption()->getText();
    $msg = sprintf('Expected option [%s] for select [%s], found [%s].',
                   $expectedText, $css, $actualText);
    $this->assert($actualText == $expectedText, $msg);
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

  protected function assertNoLink(string $linkText): void {
    try {
      $link = $this->getLinkByText($linkText);
      throw new Exception("Unwanted link [{$linkText}] found.");
    } catch (NoSuchElementException $e) {
    }
  }

  protected function assertLoggedInAs(string $expectedUsername): void {
    $actualUsername = $this->getIdentityUsername();
    $msg = sprintf('Expected to be logged in as [%s], found [%s]',
                   $expectedUsername, $actualUsername);
    $this->assert($actualUsername == $expectedUsername, $msg);
  }

  protected function assertOnHomePage(): void {
    $actualUrl = $this->driver->getCurrentUrl();
    $msg = sprintf('Expected to be on the homepage, found ourselves on [%s]', $actualUrl);
    $this->assert($actualUrl == $this->homepageUrl, $msg);
  }

  protected function assertOnLoginPage(): void {
    $actualUrl = $this->driver->getCurrentUrl();
    $msg = sprintf('Expected to be on the login page, found ourselves on [%s]', $actualUrl);
    $this->assert($actualUrl == url_login(), $msg);
  }

  protected function assertOnTaskPage(string $taskId): void {
    $this->assertTextExists("{$taskId}.in");
    $this->assertTextExists("{$taskId}.out");
  }

  protected function assertOnTaskEditPage(string $taskId): void {
    $this->assertTextExists('Editare enunț');
    $this->assertInputValue('#form_title', $taskId);
  }

  protected function assertLoginRequired(): void {
    $this->assertOnLoginPage();
    $this->assertTextExists('Mai întâi trebuie să te autentifici.');
  }

  protected function assertPermissionError(): void {
    $this->assertOnHomePage();
    $this->assertTextExists('Nu ai permisiuni suficiente pentru a executa această acțiune!');
  }

  abstract function run(): void;
}
