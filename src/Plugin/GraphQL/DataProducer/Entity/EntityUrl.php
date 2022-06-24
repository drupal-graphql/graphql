<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the URL of an entity.
 *
 * @DataProducer(
 *   id = "entity_url",
 *   name = @Translation("Entity url"),
 *   description = @Translation("Returns the entity's url."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Url")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "options" = @ContextDefinition("any",
 *       label = @Translation("URL Options"),
 *       description = @Translation("Options to pass to the toUrl call"),
 *       required = FALSE
 *     ),
 *     "rel" = @ContextDefinition("any",
 *       label = @Translation("Relationship type"),
 *       description = @Translation("The relationship type, e.g. canonical"),
 *       required = FALSE
 *     ),
 *   }
 * )
 */
class EntityUrl extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to create a canonical URL for.
   * @param null|array $options
   *   The options to provided to the URL generator.
   * @param null|string $rel
   *   The link relationship type, for example: canonical or edit-form.
   *
   * @return \Drupal\Core\Url
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function resolve(EntityInterface $entity, ?array $options, ?string $rel = 'canonical') {
    return $entity->toUrl($rel, $options ?? []);
  }

}
