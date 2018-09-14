<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "entity_language",
 *   name = @Translation("Entity language"),
 *   description = @Translation("Returns the entity language."),
 *   produces = @ContextDefinition("language",
 *     label = @Translation("Language")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class EntityLanguage extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return \Drupal\Core\Language\LanguageInterface
   */
  public function resolve(EntityInterface $entity) {
    return $entity->language();
  }

}
