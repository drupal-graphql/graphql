<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\field\Entity\FieldConfig;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "status" property from a given field definition.
 */
#[DataProducer(
  id: 'entity_definition_field_status',
  name: new TranslatableMarkup('Entity definition field status'),
  description: new TranslatableMarkup('Return entity definition field status.'),
  produces: new ContextDefinition(
    data_type: 'boolean',
    label: new TranslatableMarkup('Entity definition field status'),
  ),
  consumes: [
    'entity_definition_field' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Entity definition field'),
    ),
  ],
)]
class Status extends DataProducerPluginBase {

  /**
   * Resolves the "status" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return bool
   *   If the field config is enabled or not.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field): bool {
    if ($entity_definition_field instanceof BaseFieldDefinition) {
      /** @var \Drupal\Core\Field\BaseFieldDefinition $entity_definition_field */
      return TRUE;
    }
    elseif ($entity_definition_field instanceof FieldConfig) {
      /** @var \Drupal\field\Entity\FieldConfig $entity_definition_field */
      return $entity_definition_field->status();
    }
    elseif ($entity_definition_field instanceof BaseFieldOverride) {
      /** @var \Drupal\Core\Field\Entity\BaseFieldOverride $entity_definition_field */
      return $entity_definition_field->status();
    }

    return FALSE;
  }

}
