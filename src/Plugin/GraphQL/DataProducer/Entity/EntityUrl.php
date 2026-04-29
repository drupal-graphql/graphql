<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the URL of an entity.
 */
#[DataProducer(
  id: "entity_url",
  name: new TranslatableMarkup("Entity url"),
  description: new TranslatableMarkup("Returns the entity's url."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Url"),
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
    ),
    "rel" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Relationship type"),
      // @phpstan-ignore-next-line Wrong doc comment in core.
      description: new TranslatableMarkup("The relationship type, e.g. canonical"),
      required: FALSE,
    ),
    "options" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("URL Options"),
      // @phpstan-ignore-next-line Wrong doc comment in core.
      description: new TranslatableMarkup("Options to pass to the toUrl call"),
      required: FALSE,
    ),
  ],
)]
class EntityUrl extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to create a canonical URL for.
   * @param string|null $rel
   *   The link relation type, for example: canonical or edit-form.
   * @param array|null $options
   *   The options to provided to the URL generator.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *   When the entity cannot provide a URL.
   *
   * @return \Drupal\Core\Url|null
   *   The URL object for the entity with the given relation or NULL if it does
   *   not yet have a URL.
   */
  public function resolve(EntityInterface $entity, ?string $rel, ?array $options): ?Url {
    if ($entity->isNew()) {
      return NULL;
    }
    return $entity->toUrl($rel ?? 'canonical', $options ?? []);
  }

}
