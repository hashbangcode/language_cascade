<?php

namespace Drupal\language_cascade\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\language_cascade\LanguageSwapper;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filters by language cascade.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("language_cascade")
 */
class LanguageCascade extends FilterPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The language swapper.
   *
   * @var \Drupal\language_cascade\LanguageSwapper|null
   */
  protected $languageSwapper;

  /**
   * Constructs a new LanguageFilter instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );

    $instance->setLanguageSwapper($container->get('language_cascade.language_swapper'));
    return $instance;
  }

  /**
   * Set the language swapper instance.
   *
   * @param \Drupal\language_cascade\LanguageSwapper $languageSwapper
   *   The language swapper service.
   */
  public function setLanguageSwapper(LanguageSwapper $languageSwapper) {
    $this->languageSwapper = $languageSwapper;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = $this->t('Filter by Language Cascade');
    $this->value = $this->t('Current language');
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $currentLanguage = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    $languages = $this->languageSwapper->lanquageCascadeSequence($currentLanguage);

    $this->ensureMyTable();
    // Condition by possible language fallback codes.
    $this->query->addWhere($this->options['group'], "$this->tableAlias.$this->realField", $languages, "IN");
  }

  /**
   * {@inheritDoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $form = [];
    $form['description'] = [
      '#markup' => '<p>' . $this->t('This plugin has no options.') . '</p>',
    ];
  }

}
