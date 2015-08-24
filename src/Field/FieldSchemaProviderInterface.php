<?php

/**
 * @file
 * Contains \Drupal\graphql\Field\FieldSchemaProviderInterface.
 */

namespace Drupal\graphql\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\EntitySchemaProviderInterface;

interface FieldSchemaProviderInterface {
  /**
   * @param EntitySchemaProviderInterface $entity_schema_provider
   * @param FieldDefinitionInterface $field_definition
   *
   * @return array
   */
  public function getQuerySchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition);

  /**
   * @param EntitySchemaProviderInterface $entity_schema_provider
   * @param FieldDefinitionInterface $field_definition
   *
   * @return array
   */
  public function getMutationSchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition);

  /**
   * @param FieldDefinitionInterface $field_definition
   *
   * @return bool
   */
  public function applies(FieldDefinitionInterface $field_definition);
}
