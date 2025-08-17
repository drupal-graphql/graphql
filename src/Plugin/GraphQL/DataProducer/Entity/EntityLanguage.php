<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the language of an entity.
 */
#[DataProducer(
  id: "entity_language",
  name: new TranslatableMarkup("Entity language"),
  description: new TranslatableMarkup("Returns the entity language."),
  produces: new ContextDefinition(
    data_type: "language",
    label: new TranslatableMarkup("Language")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
  ]
)]
class EntityLanguage extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity): LanguageInterface {
    return $entity->language();
  }

}
