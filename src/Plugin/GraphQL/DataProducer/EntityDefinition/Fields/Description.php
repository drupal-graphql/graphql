<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "description" from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_description",
 *   name = @Translation("Entity definition field description"),
 *   description = @Translation("Return entity definition field description."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Entity definition field description")
 *   )
 * )
 */
class Description extends DataProducerPluginBase {

  /**
   * Resolves the field description.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string|null
   *   The description.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): ?string {
    /** @var \Drupal\Component\Render\MarkupInterface|string|null $description */
    $description = $entity_definition_field->getDescription();
    // Convert translation object to string.
    if ($description instanceof MarkupInterface) {
      return (string) $description;
    }
    return $description;
  }

}
