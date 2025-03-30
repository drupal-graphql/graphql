<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the language of an entity.
 *
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
   * Resolver.
   */
  public function resolve(EntityInterface $entity): LanguageInterface {
    return $entity->language();
  }

}
