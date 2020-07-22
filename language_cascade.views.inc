<?php

/**
 * @file
 * Contains language_cascade.views.inc.
 */

/**
 * Implements hook_views_plugins_filter_alter().
 */
function language_cascade_views_plugins_filter_alter(array &$plugins) {
  if (isset($plugins['search_api_language'])) {
    // Swap out the old search_api_language class filter with our new one.
    // The old class is Drupal\search_api\Plugin\views\filter\SearchApiLanguage.
    $plugins['search_api_language']['class'] = 'Drupal\language_cascade\Plugin\views\filter\SearchApiLanguageCascade';
  }
}

/**
 * Implements hook_views_data().
 */
function language_cascade_views_data() {
  // Lang cascade views plugin filter for nodes.
  $data['node_field_data']['language_cascade'] = [
    'title' => t('Language cascade filter'),
    'filter' => [
      'title' => t('Language cascade'),
      'help' => t('Applies language cascade filter.'),
      'field' => 'langcode',
      'id' => 'language_cascade',
    ],
  ];

  // Lang cascade views plugin filter for taxonomy terms.
  $data['taxonomy_term_field_data']['language_cascade'] = [
    'title' => t('Language cascade filter'),
    'filter' => [
      'title' => t('Language cascade'),
      'help' => t('Applies language cascade filter.'),
      'field' => 'langcode',
      'id' => 'language_cascade',
    ],
  ];
  return $data;
}
