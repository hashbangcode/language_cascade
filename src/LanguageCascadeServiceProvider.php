<?php

namespace Drupal\language_cascade;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class LanguageCascadeServiceProvider.
 *
 * @package Drupal\language_cascade
 */
class LanguageCascadeServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the alias manager with our own class.
    $aliasManagerDefinition = $container->getDefinition('path.alias_manager');
    $aliasManagerDefinition->setClass('Drupal\language_cascade\Path\LanguageCascadeAliasManager');

    // Add the language swapper as a dependency to this new service.
    $aliasManagerDefinition->addArgument(new Reference('language_cascade.language_swapper'));
  }

}
