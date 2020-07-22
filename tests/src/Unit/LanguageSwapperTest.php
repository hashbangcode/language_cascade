<?php

namespace Drupal\Tests\language_cascade\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\language_cascade\LanguageSwapper;

/**
 * Defines an abstract test base for group kernel tests.
 */
class LanguageSwapperTest extends UnitTestCase {

  /**
   * Test the language swap returns the correct language.
   *
   * @dataProvider languageSwapDataProvider
   */
  public function testLanguageSwap($language, $result) {
    // Sort out the language manager mock.
    $languageObject = $this->getMockBuilder('\Drupal\core\Language\Language')
      ->disableOriginalConstructor()
      ->getMock();

    $languageManager = $this->getMockBuilder('\Drupal\Core\Language\LanguageManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $languageManager->method('getLanguage')
      ->with($result)
      ->willReturn($languageObject);

    // Mock out the LanguageSwapper class.
    $service = $this->getMockBuilder(LanguageSwapper::class)
      ->setConstructorArgs([$languageManager])
      ->enableProxyingToOriginalMethods()->getMock();

    $this->assertEquals($result, $service->swapLanguage($language));
  }

  /**
   * Data provider for testLanguageSwap().
   *
   * @return array
   *   The test data.
   */
  public function languageSwapDataProvider() {
    return [
      ['zh-hant-tw', 'zh-hant'],
      ['zh-hans-tw', 'zh-hans'],
      ['es-un', 'es'],
      ['fr-ca', 'fr'],
      ['en', 'en'],
      ['fr', 'fr'],
    ];
  }

}
