<?php

namespace Drupal\language_cascade\ParamConverter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\ParamConverter\EntityConverter;
use Drupal\language_cascade\LanguageSwapper;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Class LanguageCascadeEntityConverter.
 *
 * Overrides the default Drupal\Core\ParamConverter\EntityConverter class in
 * order to allow us to swap the displayed languages of entities.
 *
 * @package Drupal\language_cascade\ParamConverter
 */
class LanguageCascadeEntityConverter extends EntityConverter {

  /**
   * The language swapper.
   *
   * @var \Drupal\language_cascade\LanguageSwapper|null
   */
  protected $languageSwapper;

  /**
   * Constructs a new EntityConverter.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface|null $language_manager
   *   (optional) The language manager. Defaults to none.
   * @param \Drupal\language_cascade\LanguageSwapper|null $languageSwapper
   *   (optional) The language swapper.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager = NULL, LanguageSwapper $languageSwapper = NULL) {
    parent::__construct($entity_type_manager, $entity_repository);

    $this->languageManager = $language_manager;
    $this->languageSwapper = $languageSwapper;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $entity = parent::convert($value, $definition, $name, $defaults);

    // If the entity type is translatable, ensure we return the proper
    // translation object for the current context.
    if ($entity instanceof EntityInterface && $entity instanceof TranslatableInterface) {
      $langcode = $this->languageManager()
        ->getCurrentLanguage(LanguageInterface::TYPE_URL)
        ->getId();

      // If the language of the translated item:
      // - Does not equal the current language.
      // - Is the site default language.
      // Then attempt to find the next best translation available.
      if ($entity->language()->getId() != $langcode && $entity->language()->getId() == $entity->getUntranslated()->language()->getId()) {

        $newLangcode = $this->languageSwapper->swapLanguage($langcode);
        if ($langcode != $newLangcode) {
          // If the new langcode we found is different then attempt to find the
          // translation corresponding to the new langcode.
          try {
            $translation = $entity->getTranslation($newLangcode);
            if ($translation) {
              // If a translation was found then make this the current entity.
              $entity = $translation;
            }
          }
          catch (\Exception $e) {
            // Do nothing, this is an actual 404 page.
          }
        }
      }
    }

    return $entity;
  }

}
