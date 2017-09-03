<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces;

use Drupal\graphql_core\GraphQL\InterfacePluginBase;

/**
 * Plugin for GraphQL interfaces derived from Drupal entity types.
 *
 * @GraphQLInterface(
 *   id = "entity",
 *   name = "Entity",
 *   data_type = "entity"
 * )
 */
class Entity extends InterfacePluginBase {

}
