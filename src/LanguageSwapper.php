<?php

namespace Drupal\language_cascade;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Class LanguageSwapper.
 *
 * @package Drupal\language_cascade
 */
class LanguageSwapper {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new EntityConverter.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface|null $language_manager
   *   The language manager. Defaults to none.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * Convert the language code to something else.
   *
   * @param string $langcode
   *   (optional) The langcode to swap.
   *
   * @return string
   *   The new langcode if found, or the existing langcode if not.
   */
  public function swapLanguage($langcode = NULL) {
    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();

    // Split the language into parts.
    $languageParts = explode('-', $langcode);

    $numberOfLanguageParts = count($languageParts);

    if ($numberOfLanguageParts > 1) {
      // If we have found three parts to the language string then snip off the
      // end element and create a new language string. This will convert
      // strings like zh-hant-tw into zh-hant or fr-ca to fr.
      $newLangcode = implode('-', array_slice($languageParts, 0, $numberOfLanguageParts - 1));

      if ($this->languageManager->getLanguage($newLangcode)) {
        // We have found a viable language, so return that.
        return $newLangcode;
      }
    }

    return $langcode;
  }

  /**
   * Return langcodes array in cascade sequence, starting with current language.
   *
   * @param string $langcode
   *   (optional) The langcode to start the sequence from. Defaults to current.
   *
   * @return array
   *   Array of langcodes in language cascade sequence.
   */
  public function lanquageCascadeSequence($langcode = NULL) {
    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    $sequence = [];

    // First: the current language.
    $sequence[] = $langcode;

    // Second: the swapped language if it hasn't already been included.
    $swappedLangcode = $this->swapLanguage($langcode);
    if (!in_array($swappedLangcode, $sequence)) {
      $sequence[] = $swappedLangcode;
    }

    // Third: add the default language if it hasn't already been included.
    $defaultLanguage = $this->languageManager->getDefaultLanguage()->getId();
    if (!in_array($defaultLanguage, $sequence)) {
      $sequence[] = $defaultLanguage;
    }

    // Forth: add the undefined language as the final fallback.
    $sequence[] = LanguageInterface::LANGCODE_NOT_SPECIFIED;

    return $sequence;
  }

}
