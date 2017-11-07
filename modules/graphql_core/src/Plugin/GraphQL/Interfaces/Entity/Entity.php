<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;
use Drupal\graphql\Utility\StringHelper;

/**
 * Plugin for GraphQL interfaces derived from Drupal entity types.
 *
 * @GraphQLInterface(
 *   id = "entity",
 *   name = "Entity",
 *   description = @Translation("Common entity interface containing generic entity properties."),
 *   data_type = "entity"
 * )
 */
class Entity extends InterfacePluginBase {

  /**
   * Returns name of the bundle.
   *
   * @param string $entityTypeId
   *   The entity type.
   *
   * @return string
   */
  public static function getId($entityTypeId) {
    return StringHelper::camelCase($entityTypeId);
  }

}
