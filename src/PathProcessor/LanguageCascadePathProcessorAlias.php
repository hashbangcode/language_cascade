<?php

namespace Drupal\language_cascade\PathProcessor;

use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Drupal\path_alias\PathProcessor\AliasPathProcessor;
use Drupal\language_cascade\LanguageSwapper;

/**
 * Processes the inbound path using path alias lookups.
 */
class LanguageCascadePathProcessorAlias extends AliasPathProcessor {

  /**
   * The language swapper.
   *
   * @var \Drupal\language_cascade\LanguageSwapper|null
   */
  protected $languageSwapper;

  /**
   * Constructs a PathProcessorAlias object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   An alias manager for looking up the system path.
   * @param \Drupal\language_cascade\LanguageSwapper|null $languageSwapper
   *   (optional) The language swapper.
   */
  public function __construct(AliasManagerInterface $alias_manager, LanguageSwapper $languageSwapper = NULL) {
    $this->aliasManager = $alias_manager;
    $this->languageSwapper = $languageSwapper;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    $path = parent::processOutbound($path, $options, $request, $bubbleable_metadata);

    // Extract the language from the options (if possible).
    $langcode = isset($options['language']) ? $options['language']->getId() : NULL;

    if (preg_match('/node\/\d+$/', $path) || preg_match('/taxonomy\/term\/\d+$/', $path)) {
      // If a system path is returned then try to find the real alias from
      // a translation in the language cascade sequence.
      $path = $this->generateLanguagePath($path, $langcode);
    }

    return $path;
  }

  /**
   * Find out the actual path of a node/x path (if available).
   *
   * @param string $path
   *   The path.
   * @param string $langcode
   *   (optional) The lang code.
   *
   * @return string
   *   The return path.
   */
  public function generateLanguagePath($path, $langcode = NULL) {
    $languages = $this->languageSwapper->lanquageCascadeSequence($langcode);

    foreach ($languages as $newLangcode) {
      $newPath = $this->aliasManager->getAliasByPath($path, $newLangcode);

      if ($newPath != $path) {
        $path = $newPath;
        break;
      }
    }

    return $path;
  }

}
