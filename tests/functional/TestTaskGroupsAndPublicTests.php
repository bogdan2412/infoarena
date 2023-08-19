<?php

class TestTaskGroupsAndPublicTests extends FunctionalTest {

  function run(): void {
    $this->testGroupErrors();
    $this->testPublicTestsErrors();
  }

  private function testGroupErrors(): void {
    $this->setup();
    $this->assertGroupError('1-2-3', 'Prea multe caractere „-” în intervalul 1-2-3.');
    $this->assertGroupError('3-1', 'Inversiune în intervalul 3-1.');
    $this->assertGroupError(';', 'Numerele trebuie să conțină cel puțin o cifră.');
    $this->assertGroupError('abcd', 'Numărul [abcd] conține altceva decît cifre.');
    $this->assertGroupError('2000', 'Numărul 2000 este prea mare.');
    $this->assertGroupError('3,3', 'Valoarea 3 este duplicată.');
    $this->assertGroupError('1-2,2-4', 'Valoarea 2 este duplicată.');
    $this->assertGroupError('0-3', 'Numerele testelor trebuie să fie cel puțin 1.');
    $this->assertGroupError('3-6', 'Testul 6 depășește numărul de teste (5).');
    $this->assertGroupError('1-2;2-4', 'Testul 2 apare în mai multe grupe.');
    $this->assertGroupError('1-2;4-5', 'Testul 3 nu este cuprins în niciun grup.');
  }

  private function testPublicTestsErrors(): void {
    $this->setup();
    $this->assertPublicTestError('1-2-3', 'Prea multe caractere „-” în intervalul 1-2-3.');
    $this->assertPublicTestError('3-1', 'Inversiune în intervalul 3-1.');
    $this->assertPublicTestError('1;2', 'Numărul [1;2] conține altceva decît cifre.');
    $this->assertPublicTestError('abcd', 'Numărul [abcd] conține altceva decît cifre.');
    $this->assertPublicTestError('2000', 'Numărul 2000 este prea mare.');
    $this->assertPublicTestError('3,3', 'Valoarea 3 este duplicată.');
    $this->assertPublicTestError('1-2,2-4', 'Valoarea 2 este duplicată.');
    $this->assertPublicTestError('0-3', 'Numerele testelor trebuie să fie cel puțin 1.');
    $this->assertPublicTestError('3-6', 'Testul 6 depășește numărul de teste (5).');
  }

  private function assertGroupError(string $descriptor, string $expectedError): void {
    $this->assertFieldError('#form_test_groups', $descriptor,
                            '#field_test_groups .fieldError', $expectedError);
  }

  private function assertPublicTestError(string $descriptor, string $expectedError): void {
    $this->assertFieldError('#form_public_tests', $descriptor,
                            '#field_public_tests .fieldError', $expectedError);
  }

  private function assertFieldError(string $fieldCss, string $descriptor,
                                    string $errorCss, string $expectedError): void {
    $this->changeInput($fieldCss, $descriptor);
    $this->clickButton('Salvează');
    $this->assertTextExists('Sunt erori în datele introduse.');
    $elem = $this->getElementByCss($errorCss);
    $this->assert($elem->getText() == $expectedError,
                  "Did not encounter expected error: $expectedError");
  }

  private function setup(): void {
    $this->login('admin', '1234');
    $this->visitTaskEditPage('task1');
    $this->clickLinkByText('Parametri');
  }

}
