<?php

namespace Drupal\language_cascade\Plugin\Condition;

use Drupal\system\Plugin\Condition\RequestPath;
use Drupal\language_cascade\LanguageSwapper;

/**
 * Overrides core 'Request Path' condition plugin with language cascade.
 */
class LanguageCascadeRequestPath extends RequestPath {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // Convert path to lowercase. This allows comparison of the same path
    // with different case. Ex: /Page, /page, /PAGE.
    $pages = mb_strtolower($this->configuration['pages']);
    if (!$pages) {
      return TRUE;
    }

    $request = $this->requestStack->getCurrentRequest();
    // Compare the lowercase path alias (if any) and internal path.
    $path = $this->currentPath->getPath($request);
    // Do not trim a trailing slash if that is the complete path.
    $path = $path === '/' ? $path : rtrim($path, '/');

    // Language cascade logic only applied to node and term pages.
    if (preg_match('/node\/\d+$/', $path) || preg_match('/taxonomy\/term\/\d+$/', $path)) {

      // In language cascade sequence, attempt to get an aliased path for the
      // current path.
      $language_swapper = new LanguageSwapper(\Drupal::languageManager());
      $languages = $language_swapper->lanquageCascadeSequence();

      foreach ($languages as $langcode) {
        $path_alias = mb_strtolower($this->aliasManager->getAliasByPath($path, $langcode));

        if ($path_alias != $path) {
          break;
        }
      }
    }
    else {
      $path_alias = mb_strtolower($this->aliasManager->getAliasByPath($path));
    }

    return $this->pathMatcher->matchPath($path_alias, $pages) || (($path != $path_alias) && $this->pathMatcher->matchPath($path, $pages));
  }

}
