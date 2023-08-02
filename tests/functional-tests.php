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
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCheckboxes;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

if (!Config::DEVELOPMENT_MODE || !Config::TESTING_MODE) {
  print "To run functional tests, please set DEVELOPMENT_MODE = true and TESTING_MODE = true " .
    "in Config.php\n";
  exit;
}

db_connect();

$testNames = $argv;
array_shift($testNames);

$suite = new TestSuite($testNames);
$suite->run();
$suite->tearDown();
printf("Test suite completed with %d failures.\n", $suite->getNumFailures());

class TestSuite {
  const DRIVER_URL = 'http://localhost:4444';

  private array $testNames;
  private RemoteWebDriver $driver;
  private string $homepageUrl;
  private int $numFailures = 0;

  function __construct(array $testNames) {
    $this->testNames = $testNames;
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
    $firefoxOptions->setPreference('browser.download.folderList', 2);
    $firefoxOptions->setPreference('browser.download.dir', '/tmp');
    $firefoxOptions->setPreference('browser.helperApps.neverAsk.saveToDisk', 'text/plain');

    $capabilities = DesiredCapabilities::firefox();
    $capabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
    return $capabilities;
  }

  private function findAndRunTests(): void {
    $files = (empty($this->testNames))
      ? $this->findAllTests()
      : $this->findSpecificTests();

    foreach ($files as $file) {
      $this->setupAndRunTest($file);
    }
  }

  private function findAllTests(): array {
    $wildcard = __DIR__ . '/functional/Test*.php';
    return glob($wildcard);
  }

  private function findSpecificTests(): array {
    $files = [];
    foreach ($this->testNames as $testName) {
      $path = sprintf('%s/functional/%s.php', __DIR__, $testName);
      $files[] = $path;
    }
    return $files;
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
      print $e->getTraceAsString() . "\n";
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

  private function getElements(WebDriverBy $locator): array {
    return $this->driver->findElements($locator);
  }

  protected function getLinkByText(string $text): RemoteWebElement {
    return $this->getElement(WebDriverBy::linkText($text));
  }

  protected function getLinksByText(string $text): array {
    return $this->getElements(WebDriverBy::linkText($text));
  }

  protected function getElementByCss(string $cssSelector): RemoteWebElement {
    return $this->getElement(WebDriverBy::cssSelector($cssSelector));
  }

  protected function getElementByXpath(string $xpath): RemoteWebElement {
    return $this->getElement(WebDriverBy::xpath($xpath));
  }

  protected function getHiddenElementText(string $cssSelector): string {
    // Necessary because getText() returns '' for hidden elements.
    $elem = $this->getElementByCss($cssSelector);
    return $elem->getDomProperty('innerHTML');
  }

  protected function getCheckboxesByCss(string $css): WebDriverCheckboxes {
    $elem = $this->getElementByCss($css);
    return new WebDriverCheckboxes($elem);
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

  protected function visitAttachmentList(string $page): void {
    $this->driver->get(Config::URL_HOST . url_attachment_list($page));
  }

  protected function visitChangesPage(): void {
    $this->driver->get(Config::URL_HOST . url_changes());
  }

  protected function visitMonitorPage(): void {
    $this->driver->get(Config::URL_HOST . url_monitor());
  }

  protected function visitRoundPage(string $roundId): void {
    $this->driver->get(Config::URL_HOST . url_round($roundId));
  }

  protected function visitRoundEditPage(string $roundId): void {
    $this->driver->get(Config::URL_HOST . url_round_edit($roundId));
  }

  protected function visitTaskPage(string $taskId): void {
    $this->driver->get(Config::URL_HOST . url_task($taskId));
  }

  protected function visitTaskEditPage(string $taskId): void {
    $this->driver->get(Config::URL_HOST . url_task_edit($taskId));
  }

  protected function visitTextblockPage(string $page): void {
    $this->driver->get(Config::URL_HOST . url_textblock($page));
  }

  protected function visitTextblockAttachPage(string $page): void {
    $this->driver->get(Config::URL_HOST . url_attachment_new($page));
  }

  protected function visitTextblockEditPage(string $page): void {
    $this->driver->get(Config::URL_HOST . url_textblock_edit($page));
  }

  protected function visitTextblockHistoryPage(string $page): void {
    $this->driver->get(Config::URL_HOST . url_textblock_history($page));
  }

  protected function visitTextblockMovePage(string $page): void {
    $this->driver->get(Config::URL_HOST . url_textblock_move($page));
  }

  protected function visitTextblockRestorePage(string $page, int $version): void {
    $this->driver->get(Config::URL_HOST . url_textblock_restore($page, $version));
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

  protected function waitForPageLoad(string $url): void {
    $cond = WebDriverExpectedCondition::urlIs($url);
    $this->driver->wait()->until($cond);
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

  protected function downloadLink(string $linkText, string $fileName): string {
    $this->clickLinkByText($linkText);
    $dest = '/tmp/' . $fileName;
    $contents = file_get_contents($dest);
    unlink($dest);
    return $contents;
  }

  protected function changeInput(string $css, string $text): void {
    $elem = $this->getElementByCss($css);
    $elem->clear();
    $elem->sendKeys($text);
  }

  protected function setFileInput(string $css, string $filePath): void {
    $elem = $this->getElementByCss($css);
    $elem->setFileDetector(new LocalFileDetector());
    $elem->sendKeys($filePath);
  }

  protected function changeSelect(string $css, string $visibleText): void {
    $sel = $this->getSelectByCss($css);
    $sel->selectByVisibleText($visibleText);
  }

  protected function acceptConfirmationPopup(): void {
    $this->driver->switchTo()->alert()->accept();
  }

  protected function ensureOnSite(): void {
    $url = $this->driver->getCurrentUrl();
    if (!Str::startsWith($url, $this->homepageUrl)) {
      $this->driver->get($this->homepageUrl);
    }
  }

  protected function ensureLoggedOut() {
    $this->ensureOnSite();
    $identity = $this->getIdentityUsername();
    if ($identity) {
      $this->clickLinkByText('logout');
    }
  }

  protected function login(string $username, string $password) {
    $this->ensureOnSite();
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

  protected function assertTableCellText(
    string $css, int $row, int $column, string $text): void {
    $selector = sprintf('%s tr:nth-child(%d) td:nth-child(%d)', $css, $row, $column);
    $cell = $this->getElementByCss($selector);
    $actualText = $cell->getText();
    $msg = sprintf('Expected text [%s] in table [%s] row %d column %d, found [%s].',
                   $text, $css, $row, $column, $actualText);
    $this->assert($actualText == $text, $msg);
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

  protected function assertSelectNumOptions(string $css, int $expectedNumOptions): void {
    $sel = $this->getSelectByCss($css);
    $actualNumOptions = count($sel->getOptions());
    $msg = sprintf('Expected %d options in select [%s], found %d.',
                   $expectedNumOptions, $css, $actualNumOptions);
    $this->assert($actualNumOptions == $expectedNumOptions, $msg);
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

  protected function assertLinkDownloadsContent(string $linkText, string $fileName) {
    $contents = $this->downloadLink($linkText, $fileName);
    $witnessFile = __DIR__ . '/attachments/' . $fileName;
    $witnessContents = file_get_contents($witnessFile);
    $msg = sprintf('Incorect contents of downloaded file %s.', $fileName);
    $this->assert($contents == $witnessContents, $msg);
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

  protected function assertOnRoundEditPage(string $roundId): void {
    $this->assertTextExists('Editare pagină');
    $this->assertInputValue('#form_title', $roundId);
  }

  protected function assertOnTaskPage(string $taskId): void {
    $this->assertTextExists("{$taskId}.in");
    $this->assertTextExists("{$taskId}.out");
  }

  protected function assertOnTaskEditPage(string $taskId): void {
    $this->assertTextExists('Editare enunț');
    $this->assertInputValue('#form_title', $taskId);
  }

  protected function assertOnTextblockPage(string $page): void {
    $actualUrl = $this->driver->getCurrentUrl();
    $expectedUrl = Config::URL_HOST . url_textblock($page);
    $msg = sprintf('Expected to be on the page for [%s], found ourselves at [%s]',
                   $page, $actualUrl);
    $this->assert($actualUrl == $expectedUrl, $msg);
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
