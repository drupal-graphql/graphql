<?php

namespace Drupal\graphql_content\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\graphql_content\Plugin\GraphQL\Types\RawValueFieldType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derive GraphQL types for raw values of drupal fields.
 */
class RawValueFieldTypeDeriver extends FieldFormatterDeriver {

  /**
   * {@inheritdoc}
   */
  protected function getDefinition($entityType, $bundle, array $displayOptions, FieldStorageDefinitionInterface $storage = NULL) {
    if ($storage) {
      return [
        'name' => RawValueFieldType::getId($entityType, $storage->getName()),
      ];
    }
  }

}
