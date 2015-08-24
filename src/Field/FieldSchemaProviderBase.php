<?php

/**
 * @file
 * Contains \Drupal\graphql\Field\FieldSchemaProviderBase.
 */

namespace Drupal\graphql\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\EntitySchemaProviderInterface;

/**
 * Abstract base class for field schema providers.
 */
abstract class FieldSchemaProviderBase implements FieldSchemaProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function getQuerySchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    return [];
  }
}
