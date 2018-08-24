<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * @GraphQLInterface(
 *   id = "entity",
 *   name = "Entity",
 *   type = "entity",
 *   description = @Translation("Common entity interface containing generic entity properties.")
 * )
 */
class Entity extends InterfacePluginBase {

}
