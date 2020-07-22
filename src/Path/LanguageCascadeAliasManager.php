<?php

namespace Drupal\language_cascade\Path;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\AliasRepositoryInterface;
use Drupal\Core\Path\AliasWhitelistInterface;
use Drupal\language_cascade\LanguageSwapper;

/**
 * The default alias manager implementation.
 */
class LanguageCascadeAliasManager extends AliasManager {

  /**
   * The language swapper.
   *
   * @var \Drupal\language_cascade\LanguageSwapper|null
   */
  protected $languageSwapper;

  /**
   * Constructs an AliasManager.
   *
   * @param \Drupal\Core\Path\AliasRepositoryInterface $aliasRepository
   *   The alias storage service.
   * @param \Drupal\Core\Path\AliasWhitelistInterface $whitelist
   *   The whitelist implementation to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend.
   * @param \Drupal\language_cascade\LanguageSwapper|null $languageSwapper
   *   (optional) The language swapper.
   */
  public function __construct(AliasRepositoryInterface $aliasRepository, AliasWhitelistInterface $whitelist, LanguageManagerInterface $language_manager, CacheBackendInterface $cache, LanguageSwapper $languageSwapper = NULL) {
    parent::__construct($aliasRepository, $whitelist, $language_manager, $cache);
    $this->languageSwapper = $languageSwapper;
  }

  /**
   * {@inheritdoc}
   */
  public function getPathByAlias($alias, $langcode = NULL) {
    $newAlias = parent::getPathByAlias($alias, $langcode);

    if (is_null($langcode)) {
      // Ensure that the language has been set so that we can see if the noPath
      // array has been filled for the current language.
      $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    }

    if (isset($this->noPath[$langcode][$alias])) {
      // Path not found.
      // If the path was not found for this language then snip off the end and
      // try to find that language instead.
      $newLangcode = $this->languageSwapper->swapLanguage($langcode);

      if ($langcode != $newLangcode && $path = $this->pathAliasRepository->lookupByAlias($alias, $newLangcode)) {
        // Add the found alias to the lookupMap array.
        $this->lookupMap[$newLangcode][$path['path']] = $alias;
        // Now that we have found the alias remove it from the noPath array.
        unset($this->noPath[$langcode][$alias]);
        return $path['path'];
      }
      elseif ($path = $this->pathAliasRepository->lookupByAlias($alias, 'en')) {
        // If the first language was not found then default to the 'en' version
        // of the path, if it exists.
        // Add the found alias to the lookupMap array.
        $this->lookupMap[$newLangcode][$path['path']] = $alias;
        // Now that we have found the alias remove it from the noPath array.
        unset($this->noPath[$langcode][$alias]);
        return $path['path'];
      }
    }

    return $newAlias;
  }

}
